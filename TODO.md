# Casilium Unit Testing TODO

## Overview
Comprehensive plan for implementing PHP unit tests across all 14 modules of the Casilium ticketing system using PHPUnit 9.3+ with Prophecy mocking framework.

## Current State
- **Framework**: Mezzio (PHP 8.2+) with Doctrine ORM
- **Test Framework**: PHPUnit 9.3+ with Prophecy mocking
- **Existing Tests**: Only basic App module handler tests (4 files)
- **Architecture**: 14 modular components with PSR-4 autoloading

## Required Configuration Updates

### composer.json Updates
Add the following test namespaces to `autoload-dev`:

```json
"autoload-dev": {
    "psr-4": {
        "AppTest\\": "test/AppTest/",
        "UserTest\\": "test/UserTest/",
        "UserAuthenticationTest\\": "test/UserAuthenticationTest/",
        "TicketTest\\": "test/TicketTest/",
        "OrganisationTest\\": "test/OrganisationTest/",
        "OrganisationContactTest\\": "test/OrganisationContactTest/",
        "OrganisationSiteTest\\": "test/OrganisationSiteTest/",
        "ServiceLevelTest\\": "test/ServiceLevelTest/",
        "MfaTest\\": "test/MfaTest/",
        "AccountTest\\": "test/AccountTest/",
        "MailServiceTest\\": "test/MailServiceTest/",
        "LoggerTest\\": "test/LoggerTest/",
        "SlackIntegrationTest\\": "test/SlackIntegrationTest/",
        "ReportTest\\": "test/ReportTest/"
    }
}
```

## Implementation Phases

### Phase 1: Critical Security Modules (Priority: CRITICAL)

#### [ ] User Module (`src/User/`)
**Test Directory**: `test/UserTest/`

**Entity Tests**:
- [ ] `UserTest.php` - User entity validation, role assignments, active status
- [ ] `RoleTest.php` - Role hierarchy, permission assignments
- [ ] `PermissionTest.php` - Permission validation and constraints

**Service Tests**:
- [ ] `AuthManagerTest.php` - Authentication logic, password validation
- [ ] `RbacManagerTest.php` - Permission checking logic, role-based access
- [ ] `UserManagerTest.php` - User creation, password hashing, validation
- [ ] `RoleManagerTest.php` - Role management operations
- [ ] `PermissionManagerTest.php` - Permission management operations

**Handler Tests**:
- [ ] `AddUserPageHandlerTest.php` - User creation form handling
- [ ] `EditUserPageHandlerTest.php` - User modification workflows
- [ ] `ListUserPageHandlerTest.php` - User listing and pagination
- [ ] `ViewUserPageHandlerTest.php` - User detail display
- [ ] `AddRolePageHandlerTest.php` - Role creation workflows
- [ ] `EditRolePageHandlerTest.php` - Role modification
- [ ] `EditRolePermissionsPageHandlerTest.php` - Permission assignment

**Middleware Tests**:
- [ ] `AuthorisationMiddlewareTest.php` - Access control validation

**Validator Tests**:
- [ ] `UserExistsValidatorTest.php` - User existence validation
- [ ] `RoleExistsValidatorTest.php` - Role existence validation
- [ ] `PermissionExistsValidatorTest.php` - Permission existence validation

**Repository Tests**:
- [ ] `UserRepositoryTest.php` - User data persistence and retrieval

#### [ ] UserAuthentication Module (`src/UserAuthentication/`)
**Test Directory**: `test/UserAuthenticationTest/`

**Entity Tests**:
- [ ] `IdentityTest.php` - Identity management and session handling

**Service Tests**:
- [ ] `AuthenticationServiceTest.php` - Login validation, session management

**Handler Tests**:
- [ ] `LoginPageHandlerTest.php` - CSRF protection, form validation
- [ ] `LogoutPageHandlerTest.php` - Session cleanup processes

**Middleware Tests**:
- [ ] `AuthenticationMiddlewareTest.php` - Authentication enforcement

#### [ ] Mfa Module (`src/Mfa/`)
**Test Directory**: `test/MfaTest/`

**Service Tests**:
- [ ] `MfaServiceTest.php` - TOTP validation, secret generation

**Handler Tests**:
- [ ] `EnableMfaHandlerTest.php` - MFA activation workflow
- [ ] `DisableMfaHandlerTest.php` - MFA deactivation workflow
- [ ] `ValidateMfaHandlerTest.php` - MFA token validation

**Middleware Tests**:
- [ ] `MfaMiddlewareTest.php` - MFA bypass logic for non-MFA users

**Form Tests**:
- [ ] `GoogleMfaFormTest.php` - MFA form validation

### Phase 2: Core Business Logic (Priority: CRITICAL)

#### [ ] Ticket Module (`src/Ticket/`)
**Test Directory**: `test/TicketTest/`

**Entity Tests**:
- [ ] `TicketTest.php` - Ticket validation, status transitions
- [ ] `TicketResponseTest.php` - Response handling and validation
- [ ] `AgentTest.php` - Agent assignment logic
- [ ] `QueueTest.php` - Queue management and assignment
- [ ] `PriorityTest.php` - Priority level validation
- [ ] `StatusTest.php` - Status transition rules
- [ ] `TypeTest.php` - Ticket type validation
- [ ] `NotificationTest.php` - Notification entity handling

**Service Tests**:
- [ ] `TicketServiceTest.php` - Ticket creation, SLA assignment, notifications
- [ ] `QueueManagerTest.php` - Queue assignment, load balancing logic
- [ ] `MailReaderTest.php` - Email parsing security and validation

**Handler Tests**:
- [ ] `CreateTicketHandlerTest.php` - Ticket creation workflow
- [ ] `EditTicketHandlerTest.php` - Ticket modification
- [ ] `ViewTicketHandlerTest.php` - Ticket detail display
- [ ] `ListTicketHandlerTest.php` - Ticket listing and filtering
- [ ] `CreateQueueHandlerTest.php` - Queue creation
- [ ] `EditQueueHandlerTest.php` - Queue modification
- [ ] `AssignQueueMembersHandlerTest.php` - Queue member assignment

**Command Tests**:
- [ ] `CloseResolvedTicketsTest.php` - Automated ticket closure
- [ ] `CreateTicketsFromEmailTest.php` - Email-to-ticket conversion
- [ ] `UpdateWaitingTicketsTest.php` - Ticket status updates
- [ ] `NotificationsTest.php` - Notification processing

**Parser Tests**:
- [ ] `EmailMessageParserTest.php` - Email parsing accuracy and security

**Repository Tests**:
- [ ] `TicketRepositoryTest.php` - Ticket data operations
- [ ] `QueueRepositoryTest.php` - Queue data management
- [ ] `TicketResponseRepositoryTest.php` - Response data handling

**Validator Tests**:
- [ ] `DateTimeValidatorTest.php` - Date/time validation logic

#### [ ] Organisation Module (`src/Organisation/`)
**Test Directory**: `test/OrganisationTest/`

**Entity Tests**:
- [ ] `OrganisationTest.php` - Organisation validation and relationships
- [ ] `DomainTest.php` - Domain validation and DNS checking

**Service Tests**:
- [ ] `OrganisationManagerTest.php` - Organisation CRUD, duplicate prevention
- [ ] `ImportExportServiceTest.php` - Data import/export integrity

**Handler Tests**:
- [ ] `OrganisationCreateHandlerTest.php` - Organisation creation workflow
- [ ] `OrganisationEditHandlerTest.php` - Organisation modification
- [ ] `OrganisationDeleteHandlerTest.php` - Organisation deletion validation
- [ ] `OrganisationListHandlerTest.php` - Organisation listing
- [ ] `OrganisationReadHandlerTest.php` - Organisation detail view
- [ ] `OrganisationSelectHandlerTest.php` - Organisation selection
- [ ] `ExportHandlerTest.php` - Data export functionality

**Repository Tests**:
- [ ] `OrganisationRepositoryTest.php` - Organisation data persistence
- [ ] `DomainRepositoryTest.php` - Domain data management

**Validator Tests**:
- [ ] `DomainValidatorTest.php` - Domain format and DNS validation
- [ ] `OrganisationNameValidatorTest.php` - Organisation name validation

**Hydrator Tests**:
- [ ] `OrganisationHydratorTest.php` - Data transformation accuracy

**Form Tests**:
- [ ] `OrganisationFormTest.php` - Form validation and data binding

### Phase 3: Supporting Business Modules (Priority: HIGH)

#### [ ] ServiceLevel Module (`src/ServiceLevel/`)
**Test Directory**: `test/ServiceLevelTest/`

**Entity Tests**:
- [ ] `SlaTest.php` - SLA validation and target handling
- [ ] `BusinessHoursTest.php` - Business hours validation
- [ ] `SlaTargetTest.php` - SLA target calculations

**Service Tests**:
- [ ] `SlaServiceTest.php` - SLA management operations
- [ ] `CalculateBusinessHoursTest.php` - Due date calculations, timezone handling

**Handler Tests**:
- [ ] `CreateSlaHandlerTest.php` - SLA creation workflow
- [ ] `EditSlaHandlerTest.php` - SLA modification
- [ ] `ViewSlaHandlerTest.php` - SLA detail display
- [ ] `ListSlaHandlerTest.php` - SLA listing
- [ ] `AssignSlaHandlerTest.php` - SLA assignment to organisations
- [ ] `CreateBusinessHoursHandlerTest.php` - Business hours creation
- [ ] `EditBusinessHoursHandlerTest.php` - Business hours modification
- [ ] `DeleteBusinessHoursHandlerTest.php` - Business hours deletion
- [ ] `ListBusinessHoursHandlerTest.php` - Business hours listing
- [ ] `CalculateDueHandlerTest.php` - Due date calculation endpoint

**Hydrator Tests**:
- [ ] `SlaHydratorTest.php` - SLA data transformation

**Form Tests**:
- [ ] `SlaFormTest.php` - SLA form validation
- [ ] `BusinessHoursFormTest.php` - Business hours form validation
- [ ] `AssignSlaFormTest.php` - SLA assignment form

#### [ ] OrganisationContact Module (`src/OrganisationContact/`)
**Test Directory**: `test/OrganisationContactTest/`

**Entity Tests**:
- [ ] `ContactTest.php` - Contact validation and relationships

**Service Tests**:
- [ ] `ContactServiceTest.php` - Contact management operations

**Handler Tests**:
- [ ] `CreateContactHandlerTest.php` - Contact creation workflow
- [ ] `EditContactHandlerTest.php` - Contact modification
- [ ] `DeleteContactHandlerTest.php` - Contact deletion
- [ ] `ListContactHandlerTest.php` - Contact listing
- [ ] `ViewContactHandlerTest.php` - Contact detail view

**Repository Tests**:
- [ ] `ContactRepositoryTest.php` - Contact data operations

**Validator Tests**:
- [ ] `PhoneNumberValidatorTest.php` - Phone format validation

**Hydrator Tests**:
- [ ] `ContactHydratorTest.php` - Contact data transformation

**Form Tests**:
- [ ] `ContactFormTest.php` - Contact form validation

#### [ ] OrganisationSite Module (`src/OrganisationSite/`)
**Test Directory**: `test/OrganisationSiteTest/`

**Entity Tests**:
- [ ] `SiteEntityTest.php` - Site validation and hierarchy
- [ ] `CountryEntityTest.php` - Country data validation

**Service Tests**:
- [ ] `SiteManagerTest.php` - Site management operations

**Handler Tests**:
- [ ] `CreateSiteHandlerTest.php` - Site creation workflow
- [ ] `EditSiteHandlerTest.php` - Site modification
- [ ] `DeleteSiteHandlerTest.php` - Site deletion
- [ ] `ListSiteHandlerTest.php` - Site listing
- [ ] `ViewSiteHandlerTest.php` - Site detail view

**Repository Tests**:
- [ ] `SiteRepositoryTest.php` - Site data operations
- [ ] `CountryRepositoryTest.php` - Country data management

**Hydrator Tests**:
- [ ] `SiteEntityHydratorTest.php` - Site data transformation

**Form Tests**:
- [ ] `SiteFormTest.php` - Site form validation

#### [ ] Account Module (`src/Account/`)
**Test Directory**: `test/AccountTest/`

**Handler Tests**:
- [ ] `AccountPageHandlerTest.php` - Account page display
- [ ] `ChangePasswordHandlerTest.php` - Password change validation

### Phase 4: Utility and Integration Modules (Priority: MEDIUM/LOW)

#### [ ] App Module (`src/App/`) - *Expand Existing Coverage*
**Test Directory**: `test/AppTest/` *(existing)*

**Command Tests**:
- [ ] `CreateSodiumKeyTest.php` - Encryption key generation
- [ ] `TestCommandTest.php` - Test command functionality

**Service Tests**:
- [ ] `SodiumTest.php` - Encryption/decryption operations

**Middleware Tests**:
- [ ] `PrgMiddlewareTest.php` - Post-Redirect-Get pattern
- [ ] `XMLHttpRequestTemplateMiddlewareTest.php` - AJAX request handling

**View Helper Tests**:
- [ ] `BreadcrumbsTest.php` - Navigation breadcrumb generation
- [ ] `FlashTest.php` - Flash message handling
- [ ] `LocalDateTest.php` - Date formatting and localization

**Doctrine Tests**:
- [ ] `UtcDateTimeTypeTest.php` - UTC datetime handling
- [ ] `UuidEncoderTest.php` - UUID encoding/decoding

**Cache Tests**:
- [ ] `FileSystemCacheFactoryTest.php` - Cache factory testing

#### [ ] MailService Module (`src/MailService/`)
**Test Directory**: `test/MailServiceTest/`

**Service Tests**:
- [ ] `MailServiceTest.php` - Email sending and template rendering

#### [ ] Logger Module (`src/Logger/`)
**Test Directory**: `test/LoggerTest/`

**Service Tests**:
- [ ] `LogServiceTest.php` - Logging operations and formatting

#### [ ] Report Module (`src/Report/`)
**Test Directory**: `test/ReportTest/`

**Service Tests**:
- [ ] `ReportServiceTest.php` - Report generation and data aggregation

**Handler Tests**:
- [ ] `ExecutiveReportHandlerTest.php` - Executive report generation

#### [ ] SlackIntegration Module (`src/SlackIntegration/`)
**Test Directory**: `test/SlackIntegrationTest/`

**Entity Tests**:
- [ ] `MessageTest.php` - Slack message entity validation

**Service Tests**:
- [ ] `ClientTest.php` - Slack API client integration

## Testing Standards and Guidelines

### Test Structure Pattern
```
test/
├── [Module]Test/
│   ├── Entity/
│   ├── Service/
│   ├── Handler/
│   ├── Repository/
│   ├── Validator/
│   ├── Middleware/
│   ├── Command/
│   ├── Form/
│   └── Hydrator/
```

### Testing Best Practices

#### Mocking Strategy
- Use **Prophecy** for all external dependencies
- Mock repository dependencies in service tests
- Mock TemplateRendererInterface for handler tests
- Use in-memory SQLite for repository integration tests

#### Test Naming Convention
- Method: `testMethodNameScenarioExpectedResult`
- Class: `[ClassName]Test.php`
- Example: `testCreateUserWithValidDataReturnsUser`

#### Security Testing Focus
- [ ] SQL injection prevention in custom queries
- [ ] XSS protection in form handlers  
- [ ] CSRF token validation
- [ ] Input sanitization and validation
- [ ] Authentication bypass attempts
- [ ] Authorization privilege escalation
- [ ] Password hashing verification
- [ ] Session management security

#### Coverage Targets
- **Critical Security Modules**: 90%+ coverage
- **Core Business Logic**: 85%+ coverage
- **Supporting Modules**: 80%+ coverage
- **Utility Modules**: 75%+ coverage

#### Data Providers Usage
Use PHPUnit data providers for:
- [ ] Multiple input validation scenarios
- [ ] Edge case testing
- [ ] Cross-browser compatibility data
- [ ] Different user role permissions

### Test Environment Setup

#### Database Configuration
```php
// Use in-memory SQLite for repository tests
$connectionParams = [
    'driver' => 'pdo_sqlite',
    'memory' => true,
];
```

#### Mock Container Setup
```php
protected function setUp(): void
{
    $this->container = $this->prophesize(ContainerInterface::class);
    $this->templateRenderer = $this->prophesize(TemplateRendererInterface::class);
}
```

## Execution Commands

### Run All Tests
```bash
composer test
```

### Run Tests with Coverage
```bash
composer test-coverage
```

### Run Specific Module Tests
```bash
vendor/bin/phpunit test/UserTest/
vendor/bin/phpunit test/TicketTest/
```

### Code Quality Checks
```bash
composer cs-check
composer cs-fix
```

## Success Criteria

### Phase Completion Criteria
- [ ] All tests in phase pass consistently
- [ ] Code coverage meets target thresholds
- [ ] No phpcs violations introduced
- [ ] Security test scenarios validate properly
- [ ] Integration tests work with test database

### Overall Project Completion
- [ ] **300+ unit tests** covering all modules
- [ ] **80%+ overall code coverage**
- [ ] **Zero security vulnerabilities** in tested code
- [ ] **Automated test execution** in CI/CD pipeline
- [ ] **Documentation** for test maintenance and extension

## Notes

### Critical Security Considerations
- Never test with production data
- Ensure all password/encryption tests use test keys
- Validate that authentication tests don't bypass security
- Test SQL injection prevention with malicious inputs
- Verify XSS protection with script injection attempts

### Performance Considerations
- Use data providers efficiently to avoid test duplication
- Mock external services to prevent network dependencies
- Use in-memory databases for fast repository testing
- Group related tests to minimize setup/teardown overhead

### Maintenance Guidelines
- Update tests when entities or business logic changes
- Add tests for new features during development
- Regular security test updates based on threat landscape
- Performance test updates for critical user workflows