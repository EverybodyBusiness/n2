---
description: 
globs: 
alwaysApply: true
---
# 프로젝트 개요
- project_name: "프로젝트 템플릿"
- version: "1.0.0"
- description: "다양한 플랫폼에 공용으로 사용하기 위한 Base Template"

## 프로젝트 시스템 구성
- backend: Laravel API 서버
- frontend: React Native Web + Next.js
- mobile: React Native
- database: MySQL

## 프로젝트 공통 정책
- 다국어 지원: 한국어만 지원
- API 형식: RESTful
- 응답 형식: JSON
- 인증 방식: Laravel Sanctum

## 프로젝트 코드 생성 규칙
- 현재 프로젝트에 구현해야 하는 모든 도메인 업무 정의서는 doc/domains 에 있고, 각 파일명은 도메인명이 되다.
- "코드재생성" 이라는 프롬프트는 각 도메인 업무 정의서에 있는 `업무 정의서`에 설명된 기능을 이해하고 새로 생성한다.
- Agent는 `doc/domains` 디렉토리의 각 `.md` 파일은 도메인명과 도메인 업무를 정의한다
