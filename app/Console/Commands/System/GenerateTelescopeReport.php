<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class GenerateTelescopeReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telescope:report 
                            {--period=daily : Report period (daily, weekly, monthly)}
                            {--email= : Send report to specific email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Telescope error report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period');
        $email = $this->option('email');

        $this->info("Generating {$period} Telescope report...");

        // 기간 설정
        $startDate = match ($period) {
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            default => now()->subDay(),
        };

        // 데이터 수집
        $reportData = $this->collectReportData($startDate);

        // 이메일 발송
        $recipients = $email ? [$email] : User::role('super_admin')->pluck('email')->toArray();
        
        if (!empty($recipients)) {
            Mail::send('emails.telescope-report', [
                'period' => $period,
                'startDate' => $startDate,
                'endDate' => now(),
                'data' => $reportData,
            ], function ($message) use ($recipients, $period) {
                $message->to($recipients)
                    ->subject('[Telescope] ' . ucfirst($period) . ' Error Report - ' . now()->format('Y-m-d'));
            });
        }

        // 로그 파일로도 저장
        $logPath = storage_path('logs/telescope-reports/');
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $filename = "telescope-{$period}-" . now()->format('Y-m-d') . '.log';
        file_put_contents($logPath . $filename, $this->formatReportAsText($reportData, $period, $startDate));

        $this->info("Report generated and sent to: " . implode(', ', $recipients));
        $this->info("Report also saved to: " . $logPath . $filename);
    }

    /**
     * Collect report data from Telescope
     */
    protected function collectReportData($startDate)
    {
        $data = [];

        // Exception 통계
        $data['exceptions'] = DB::table('telescope_entries')
            ->where('type', 'exception')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('JSON_EXTRACT(content, "$.class") as exception_class'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('exception_class')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Failed Jobs
        $data['failed_jobs'] = DB::table('telescope_entries')
            ->where('type', 'job')
            ->where('created_at', '>=', $startDate)
            ->whereRaw('JSON_EXTRACT(content, "$.status") = "failed"')
            ->count();

        // Slow Queries
        $data['slow_queries'] = DB::table('telescope_entries')
            ->where('type', 'query')
            ->where('created_at', '>=', $startDate)
            ->whereRaw('JSON_EXTRACT(content, "$.time") > 1000') // > 1초
            ->count();

        // 시간대별 에러 분포
        $data['hourly_errors'] = DB::table('telescope_entries')
            ->where('type', 'exception')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // 영향받은 사용자
        $data['affected_users'] = DB::table('telescope_entries')
            ->where('type', 'exception')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // 총 에러 수
        $data['total_errors'] = DB::table('telescope_entries')
            ->where('type', 'exception')
            ->where('created_at', '>=', $startDate)
            ->count();

        // 가장 최근 에러들
        $data['recent_errors'] = DB::table('telescope_entries')
            ->where('type', 'exception')
            ->where('created_at', '>=', $startDate)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($entry) {
                $content = json_decode($entry->content, true);
                return [
                    'id' => $entry->uuid,
                    'exception' => $content['class'] ?? 'Unknown',
                    'message' => $content['message'] ?? 'No message',
                    'file' => basename($content['file'] ?? 'Unknown'),
                    'line' => $content['line'] ?? 0,
                    'created_at' => $entry->created_at,
                ];
            });

        return $data;
    }

    /**
     * Format report data as text
     */
    protected function formatReportAsText($data, $period, $startDate)
    {
        $text = "=== Telescope Error Report ===\n";
        $text .= "Period: " . ucfirst($period) . "\n";
        $text .= "From: " . $startDate->format('Y-m-d H:i:s') . "\n";
        $text .= "To: " . now()->format('Y-m-d H:i:s') . "\n";
        $text .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";

        $text .= "=== Summary ===\n";
        $text .= "Total Errors: " . $data['total_errors'] . "\n";
        $text .= "Affected Users: " . $data['affected_users'] . "\n";
        $text .= "Failed Jobs: " . $data['failed_jobs'] . "\n";
        $text .= "Slow Queries: " . $data['slow_queries'] . "\n\n";

        $text .= "=== Top Exceptions ===\n";
        foreach ($data['exceptions'] as $exception) {
            $text .= sprintf("%-50s %d times\n", 
                str_replace('"', '', $exception->exception_class ?? 'Unknown'),
                $exception->count
            );
        }

        $text .= "\n=== Hourly Distribution ===\n";
        foreach ($data['hourly_errors'] as $hour) {
            $text .= sprintf("%02d:00 - %02d:59  %d errors\n", 
                $hour->hour, 
                $hour->hour, 
                $hour->count
            );
        }

        $text .= "\n=== Recent Errors ===\n";
        foreach ($data['recent_errors'] as $error) {
            $text .= "---\n";
            $text .= "Time: " . $error['created_at'] . "\n";
            $text .= "Exception: " . $error['exception'] . "\n";
            $text .= "Message: " . $error['message'] . "\n";
            $text .= "Location: " . $error['file'] . ":" . $error['line'] . "\n";
            $text .= "UUID: " . $error['id'] . "\n";
        }

        return $text;
    }
}
