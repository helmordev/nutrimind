<?php

declare(strict_types=1);

use App\Enums\DifficultyLevel;
use App\Enums\DifficultySetBy;
use App\Enums\Language;
use App\Enums\QuestionType;
use App\Enums\ScreenTimeScope;
use App\Enums\TextSize;
use App\Enums\UserRole;

// --- DifficultyLevel ---

it('has the correct difficulty level cases', function (): void {
    expect(DifficultyLevel::cases())->toHaveCount(3)
        ->and(DifficultyLevel::Easy->value)->toBe('easy')
        ->and(DifficultyLevel::Standard->value)->toBe('standard')
        ->and(DifficultyLevel::Hard->value)->toBe('hard');
});

it('returns correct labels for difficulty levels', function (): void {
    expect(DifficultyLevel::Easy->label())->toBe('Easy')
        ->and(DifficultyLevel::Standard->label())->toBe('Standard')
        ->and(DifficultyLevel::Hard->label())->toBe('Hard');
});

it('returns correct numeric values for difficulty levels', function (): void {
    expect(DifficultyLevel::Easy->numericValue())->toBe(1)
        ->and(DifficultyLevel::Standard->numericValue())->toBe(2)
        ->and(DifficultyLevel::Hard->numericValue())->toBe(3);
});

// --- DifficultySetBy ---

it('has the correct difficulty set by cases', function (): void {
    expect(DifficultySetBy::cases())->toHaveCount(2)
        ->and(DifficultySetBy::System->value)->toBe('system_default')
        ->and(DifficultySetBy::Teacher->value)->toBe('teacher');
});

it('returns correct labels for difficulty set by', function (): void {
    expect(DifficultySetBy::System->label())->toBe('System Default')
        ->and(DifficultySetBy::Teacher->label())->toBe('Teacher');
});

// --- Language ---

it('has the correct language cases', function (): void {
    expect(Language::cases())->toHaveCount(2)
        ->and(Language::English->value)->toBe('en')
        ->and(Language::Filipino->value)->toBe('fil');
});

it('returns correct labels for languages', function (): void {
    expect(Language::English->label())->toBe('English')
        ->and(Language::Filipino->label())->toBe('Filipino');
});

// --- QuestionType ---

it('has the correct question type cases', function (): void {
    expect(QuestionType::cases())->toHaveCount(5)
        ->and(QuestionType::MultipleChoice->value)->toBe('multiple_choice')
        ->and(QuestionType::TrueOrFalse->value)->toBe('true_or_false')
        ->and(QuestionType::Identification->value)->toBe('identification')
        ->and(QuestionType::Matching->value)->toBe('matching')
        ->and(QuestionType::Sequencing->value)->toBe('sequencing');
});

it('returns correct labels for question types', function (): void {
    expect(QuestionType::MultipleChoice->label())->toBe('Multiple Choice')
        ->and(QuestionType::TrueOrFalse->label())->toBe('True or False')
        ->and(QuestionType::Identification->label())->toBe('Identification')
        ->and(QuestionType::Matching->label())->toBe('Matching')
        ->and(QuestionType::Sequencing->label())->toBe('Sequencing');
});

// --- ScreenTimeScope ---

it('has the correct screen time scope cases', function (): void {
    expect(ScreenTimeScope::cases())->toHaveCount(3)
        ->and(ScreenTimeScope::Global->value)->toBe('global')
        ->and(ScreenTimeScope::ClassScope->value)->toBe('class')
        ->and(ScreenTimeScope::Student->value)->toBe('student');
});

it('returns correct labels for screen time scopes', function (): void {
    expect(ScreenTimeScope::Global->label())->toBe('Global')
        ->and(ScreenTimeScope::ClassScope->label())->toBe('Class')
        ->and(ScreenTimeScope::Student->label())->toBe('Student');
});

// --- TextSize ---

it('has the correct text size cases', function (): void {
    expect(TextSize::cases())->toHaveCount(3)
        ->and(TextSize::Small->value)->toBe('small')
        ->and(TextSize::Medium->value)->toBe('medium')
        ->and(TextSize::Large->value)->toBe('large');
});

it('returns correct labels for text sizes', function (): void {
    expect(TextSize::Small->label())->toBe('Small')
        ->and(TextSize::Medium->label())->toBe('Medium')
        ->and(TextSize::Large->label())->toBe('Large');
});

// --- UserRole ---

it('has the correct user role cases', function (): void {
    expect(UserRole::cases())->toHaveCount(3)
        ->and(UserRole::Student->value)->toBe('student')
        ->and(UserRole::Teacher->value)->toBe('teacher')
        ->and(UserRole::SuperAdmin->value)->toBe('super_admin');
});

it('returns correct labels for user roles', function (): void {
    expect(UserRole::Student->label())->toBe('Student')
        ->and(UserRole::Teacher->label())->toBe('Teacher')
        ->and(UserRole::SuperAdmin->label())->toBe('Super Admin');
});
