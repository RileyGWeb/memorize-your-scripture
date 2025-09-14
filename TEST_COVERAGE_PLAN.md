# Test Coverage Improvement Plan
**Goal: Increase test coverage from 56.4% to 80%**

## Current Status
- **Current Coverage**: 56.4%
- **Target Coverage**: 80%
- **Tests Passing**: 274 tests, 780 assertions
- **Coverage Gap**: 23.6% improvement needed

## Priority Areas for Testing

### 🔴 **Critical - 0% Coverage (Must Fix)**

#### 1. Console Commands
- **File**: `app/Console/Commands/VerifyLegitimateUsers.php` (0%)
- **Impact**: High - Admin functionality for user verification
- **Test File**: `tests/Feature/VerifyLegitimateUsersCommandTest.php` (create)
- **Test Cases Needed**:
  - Auto verification of users with memorized verses
  - Email-specific verification
  - Dry-run functionality
  - Error handling for missing options
  - Database changes validation

#### 2. Helper Classes
- **File**: `app/Helpers/AssetHelper.php` (0%)
- **Impact**: Medium - Vite asset management
- **Test File**: `tests/Unit/AssetHelperTest.php` (create)
- **Test Cases Needed**:
  - Vite asset path resolution
  - Hot reload detection
  - Missing manifest handling
  - Invalid asset handling

#### 3. Models
- **File**: `app/Models/MemoryBank.php` (0%)
- **Impact**: High - Core memorization functionality
- **Test File**: `tests/Unit/MemoryBankTest.php` (create)
- **Test Cases Needed**:
  - Model relationships
  - Fillable attributes
  - Type casting (verses array, memorized_at datetime)
  - Factory functionality
  - Auditable trait integration

#### 4. Livewire Components
- **File**: `app/Livewire/QuizSetup.php` (0%)
- **Impact**: High - Quiz functionality
- **Test File**: `tests/Feature/QuizSetupTest.php` (create)
- **Test Cases Needed**:
  - Component mounting and initialization
  - Quiz number increment/decrement
  - Difficulty selection
  - Quiz type handling
  - Session management
  - Redirect logic for missing setup

#### 5. Password Reset Functionality
- **File**: `app/Actions/Fortify/ResetUserPassword.php` (0%)
- **Impact**: High - User security
- **Test File**: `tests/Feature/ResetUserPasswordTest.php` (create)
- **Test Cases Needed**:
  - Password reset validation
  - Password reset execution
  - Error handling
  - Security requirements

#### 6. Event Listeners
- **File**: `app/Listeners/MarkUserAsVerified.php` (0%)
- **Impact**: Medium - User verification workflow
- **Test File**: `tests/Unit/MarkUserAsVerifiedTest.php` (create)
- **Test Cases Needed**:
  - Event handling
  - User verification logic
  - Database updates

### 🟡 **Medium Priority - Low Coverage (20-60%)**

#### 7. Controllers
- **File**: `app/Http/Controllers/MemorizationToolController.php` (26.7%)
- **Missing Coverage**: Complex methods, error handling, edge cases
- **Test File**: `tests/Feature/MemorizationToolControllerTest.php` (enhance existing)
- **Additional Test Cases Needed**:
  - Verse parsing edge cases
  - Bible API error handling
  - Invalid reference handling
  - Authentication edge cases

- **File**: `app/Http/Controllers/MemorizationController.php` (46.5%)
- **Missing Coverage**: Error paths, validation edge cases
- **Test File**: `tests/Feature/MemorizationControllerTest.php` (enhance existing)
- **Additional Test Cases Needed**:
  - Memory bank saving edge cases
  - Accuracy calculation variations
  - Different difficulty scenarios

- **File**: `app/Http/Controllers/MemoryBankController.php` (0%)
- **Impact**: High - Memory bank management
- **Test File**: `tests/Feature/MemoryBankControllerTest.php` (create)
- **Test Cases Needed**:
  - CRUD operations
  - Authorization checks
  - Data validation
  - Filtering and sorting

#### 8. Livewire Components (Partial Coverage)
- **File**: `app/Livewire/UnifiedVersePicker.php` (15.6%)
- **Missing Coverage**: Complex verse parsing, error handling
- **Test File**: `tests/Feature/UnifiedVersePickerTest.php` (enhance existing)

- **File**: `app/Livewire/Profile/UpdateBackgroundImage.php` (22.2%)
- **Missing Coverage**: File upload handling, validation
- **Test File**: `tests/Feature/UpdateBackgroundImageTest.php` (create)

- **File**: `app/Livewire/DailyQuiz.php` (64.6%)
- **Missing Coverage**: Quiz generation edge cases
- **Test File**: `tests/Feature/DailyQuizTest.php` (enhance existing)

- **File**: `app/Livewire/InstallPrompt.php` (66.7%)
- **Missing Coverage**: PWA detection logic
- **Test File**: `tests/Feature/InstallPromptTest.php` (enhance existing)

#### 9. Fortify Actions
- **File**: `app/Actions/Fortify/UpdateUserProfileInformation.php` (60.0%)
- **Missing Coverage**: Validation edge cases, email verification
- **Test File**: `tests/Feature/UpdateUserProfileInformationTest.php` (enhance existing)

- **File**: `app/Providers/FortifyServiceProvider.php` (91.7%)
- **Missing Coverage**: Custom validation rules, edge cases
- **Test File**: `tests/Unit/FortifyServiceProviderTest.php` (create)

### 🟢 **Lower Priority - Good Coverage (70-95%)**

#### 10. Models (Minor Improvements)
- **File**: `app/Models/User.php` (92.0%)
- **Missing Coverage**: Edge cases in custom methods
- **File**: `app/Models/AuditLog.php` (95.5%)
- **Missing Coverage**: Relationship edge cases

#### 11. Livewire Components (Minor Improvements)
- **File**: `app/Livewire/MemorizeLater.php` (74.8%)
- **Missing Coverage**: Complex validation scenarios
- **File**: `app/Livewire/VersePicker.php` (86.7%)
- **Missing Coverage**: Error handling edge cases
- **File**: `app/Livewire/QuizTaker.php` (90.3%)
- **Missing Coverage**: Quiz completion edge cases

## Implementation Strategy

### Phase 1: Critical Files (Weeks 1-2)
**Target: +15% coverage**
1. Create tests for 0% coverage files
2. Focus on Models and Console Commands first
3. Ensure all basic functionality is tested

### Phase 2: Medium Priority Files (Weeks 3-4)
**Target: +6% coverage**
1. Enhance existing tests for partial coverage files
2. Add edge case testing
3. Test error handling scenarios

### Phase 3: Polish and Edge Cases (Week 5)
**Target: +2.6% coverage**
1. Add comprehensive integration tests
2. Test complex workflows end-to-end
3. Add performance and stress tests

## Test File Creation Checklist

### New Test Files Needed:
- [ ] `tests/Feature/VerifyLegitimateUsersCommandTest.php`
- [ ] `tests/Unit/AssetHelperTest.php`
- [ ] `tests/Unit/MemoryBankTest.php`
- [ ] `tests/Feature/QuizSetupTest.php`
- [ ] `tests/Feature/ResetUserPasswordTest.php`
- [ ] `tests/Unit/MarkUserAsVerifiedTest.php`
- [ ] `tests/Feature/MemoryBankControllerTest.php`
- [ ] `tests/Feature/UpdateBackgroundImageTest.php`
- [ ] `tests/Unit/FortifyServiceProviderTest.php`

### Existing Tests to Enhance:
- [ ] `tests/Feature/MemorizationToolControllerTest.php`
- [ ] `tests/Feature/MemorizationControllerTest.php`
- [ ] `tests/Feature/UnifiedVersePickerTest.php`
- [ ] `tests/Feature/DailyQuizTest.php`
- [ ] `tests/Feature/InstallPromptTest.php`
- [ ] `tests/Feature/UpdateUserProfileInformationTest.php`

## Success Metrics

### Coverage Targets:
- **Phase 1 Complete**: 70%+ coverage
- **Phase 2 Complete**: 78%+ coverage
- **Phase 3 Complete**: 80%+ coverage

### Quality Gates:
- All tests must pass
- No regression in existing functionality
- Test execution time under 60 seconds
- Clear, maintainable test code
- Proper test data isolation

## Tools and Commands

### Running Coverage:
```bash
XDEBUG_MODE=coverage php artisan test --coverage
```

### Running Specific Test Files:
```bash
php artisan test tests/Feature/SpecificTest.php
```

### Creating Test Files:
```bash
php artisan make:test NameOfTest --feature
php artisan make:test NameOfTest --unit
```

## Notes

- Focus on testing business logic rather than framework code
- Use factories for consistent test data
- Mock external dependencies (Bible API, file system)
- Test both happy paths and error scenarios
- Ensure tests are isolated and can run in any order
- Consider using pest for more readable test syntax if desired

## Expected Timeline
**Total Duration**: 5 weeks
**Estimated Effort**: 2-3 hours per day
**Final Target**: 80%+ test coverage with comprehensive test suite
