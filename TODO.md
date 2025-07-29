# Doctrine Annotations to Attributes Migration Plan

## Overview

This document outlines the comprehensive plan to migrate all Doctrine entities in the Casilium project from annotation-based mapping to PHP 8+ attributes, module by module.

## Project Analysis

### Current State
- **PHP Version**: 8.2-8.4 support ✅
- **Doctrine ORM**: 2.20.5 ✅ (attributes supported since 2.9+)
- **Entity Count**: 21 entity files across 7 modules
- **Architecture**: Modular design with clear separation of concerns

### Modules with Entities
1. **User** (3 entities): User, Role, Permission
2. **Ticket** (8 entities): Ticket, Agent, Priority, Queue, QueueMember, Status, TicketResponse, Type, Notification
3. **Organisation** (2 entities): Organisation, Domain  
4. **OrganisationContact** (1 entity): Contact
5. **OrganisationSite** (2 entities): SiteEntity, CountryEntity
6. **ServiceLevel** (3 entities): Sla, SlaTarget, BusinessHours
7. **SlackIntegration** (1 entity): Message

## Migration Strategy

### Module Migration Order (Priority-Based)

#### Phase 1: Foundation Modules (Low Risk)
1. **SlackIntegration** (1 entity)
   - Minimal dependencies
   - Simple entity structure
   - Easy rollback if issues arise

2. **ServiceLevel** (3 entities)
   - Self-contained business logic
   - Limited external dependencies
   - Clear entity relationships

3. **OrganisationSite** (2 entities)
   - Geographic/location data
   - Low complexity relationships
   - Moderate dependency footprint

#### Phase 2: Core Business Logic (Medium Risk)
4. **OrganisationContact** (1 entity)
   - Simple contact management
   - Moderate dependencies on Organisation

5. **Organisation** (2 entities)
   - Core business entity
   - Referenced by multiple modules
   - Important but contained scope

#### Phase 3: Complex Interconnected (High Risk)
6. **User** (3 entities)
   - Authentication and authorization system
   - High dependency count
   - Security-critical functionality

7. **Ticket** (8 entities)
   - Most complex module
   - Highest interconnectivity
   - Business-critical functionality

### Benefits of Module-by-Module Approach
- **Isolated Risk**: Each module can be tested independently
- **Easier Debugging**: Issues can be traced to specific modules
- **Gradual Validation**: Incremental verification of changes
- **Project Stability**: Core functionality remains intact during migration
- **Easy Rollback**: Individual modules can be reverted without affecting others

## Technical Requirements

### Prerequisites
- ✅ **Doctrine ORM 2.9+**: Current version 2.20.5 fully supports attributes
- ✅ **PHP 8.0+**: Current support for 8.2-8.4 includes native attributes
- ✅ **Existing Test Suite**: For validation of migration success

### Environment Setup
- **Backup Strategy**: Create git branches for each module migration
- **Testing Environment**: Comprehensive test coverage validation
- **Cache Management**: Plan for Doctrine proxy cache clearing between migrations

## Annotation to Attribute Conversion Patterns

### Entity Definition
```php
// Before (Annotation)
/**
 * @ORM\Entity(repositoryClass="User\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User
{
    // ...
}

// After (Attribute)
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User
{
    // ...
}
```

### Basic Properties
```php
// Before (Annotation)
/**
 * @ORM\Id
 * @ORM\Column(name="id", type="integer")
 * @ORM\GeneratedValue
 * @var int
 */
private $id;

// After (Attribute)
#[ORM\Id]
#[ORM\Column(name: 'id', type: 'integer')]
#[ORM\GeneratedValue]
private int $id;
```

### String Properties
```php
// Before (Annotation)
/**
 * @ORM\Column(name="email")
 * @var string
 */
private $email;

// After (Attribute)
#[ORM\Column(name: 'email')]
private string $email;
```

### Relationships - ManyToMany
```php
// Before (Annotation)
/**
 * @ORM\ManyToMany(targetEntity="User\Entity\Role")
 * @ORM\JoinTable(name="user_role",
 *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
 *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
 * )
 * @var ArrayCollection
 */
private $roles;

// After (Attribute)
#[ORM\ManyToMany(targetEntity: Role::class)]
#[ORM\JoinTable(name: 'user_role')]
#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
#[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id')]
private Collection $roles;
```

### Relationships - OneToOne
```php
// Before (Annotation)
/**
 * @ORM\OneToOne(targetEntity="User\Entity\User")
 * @ORM\JoinColumn(name="assigned_agent_id", referencedColumnName="id", nullable=true)
 */
private User $assignedAgent;

// After (Attribute)
#[ORM\OneToOne(targetEntity: User::class)]
#[ORM\JoinColumn(name: 'assigned_agent_id', referencedColumnName: 'id', nullable: true)]
private User $assignedAgent;
```

## Module Migration Checklist

### Pre-Migration Steps
- [ ] Create feature branch: `migrate-attributes-{module-name}`
- [ ] Run existing tests: `composer test`
- [ ] Generate current schema: `vendor/bin/doctrine orm:schema-tool:create --dump-sql`
- [ ] Backup current proxy classes: `cp -r data/cache/DoctrineEntityProxy data/cache/DoctrineEntityProxy.backup`
- [ ] Document current entity relationships and dependencies

### Migration Process
- [ ] **Convert Class-Level Annotations**
  - [ ] Replace `@ORM\Entity` with `#[ORM\Entity]`
  - [ ] Replace `@ORM\Table` with `#[ORM\Table]`
  - [ ] Update repository class references to use `::class` syntax

- [ ] **Convert Property-Level Annotations**
  - [ ] Replace `@ORM\Id` with `#[ORM\Id]`
  - [ ] Replace `@ORM\Column` with `#[ORM\Column]`
  - [ ] Replace `@ORM\GeneratedValue` with `#[ORM\GeneratedValue]`
  - [ ] Update all column type definitions

- [ ] **Convert Relationship Mappings**
  - [ ] Replace `@ORM\OneToOne` with `#[ORM\OneToOne]`
  - [ ] Replace `@ORM\OneToMany` with `#[ORM\OneToMany]`
  - [ ] Replace `@ORM\ManyToOne` with `#[ORM\ManyToOne]`
  - [ ] Replace `@ORM\ManyToMany` with `#[ORM\ManyToMany]`
  - [ ] Replace `@ORM\JoinColumn` with `#[ORM\JoinColumn]`
  - [ ] Replace `@ORM\JoinTable` with `#[ORM\JoinTable]`

- [ ] **Update Type Hints and Documentation**
  - [ ] Add proper PHP 8+ type hints to properties
  - [ ] Remove `@var` comments (replaced by type hints)
  - [ ] Update method return types where appropriate
  - [ ] Ensure `Collection` type imports are correct

- [ ] **Clear Doctrine Cache**
  - [ ] Remove proxy classes: `rm -rf data/cache/DoctrineEntityProxy/*`
  - [ ] Clear any additional Doctrine caches

### Post-Migration Validation
- [ ] **Schema Validation**
  - [ ] Generate new schema: `vendor/bin/doctrine orm:schema-tool:create --dump-sql`
  - [ ] Compare with pre-migration schema (should be identical)
  - [ ] Verify no schema differences: `vendor/bin/doctrine orm:schema-tool:update --dump-sql`

- [ ] **Testing**
  - [ ] Run all unit tests: `composer test`
  - [ ] Run integration tests if available
  - [ ] Test entity relationships in application
  - [ ] Validate repository methods work correctly
  - [ ] Check entity hydration and serialization

- [ ] **Proxy and Cache Validation**
  - [ ] Verify Doctrine proxy classes generate correctly
  - [ ] Test entity lazy loading functionality
  - [ ] Validate query performance (should be unchanged)

- [ ] **Database Operations**
  - [ ] Test CRUD operations for all entities in module
  - [ ] Verify existing migrations are unaffected
  - [ ] Test complex queries and joins involving module entities

### Quality Assurance
- [ ] **Code Standards**
  - [ ] Run code standards check: `composer cs-check`
  - [ ] Fix any coding standard violations: `composer cs-fix`
  - [ ] Verify PSR-12 compliance

- [ ] **Code Review**
  - [ ] Check for annotation remnants: `grep -r "@ORM" src/{ModuleName}/`
  - [ ] Validate no breaking changes in public APIs
  - [ ] Review PHPDoc blocks for accuracy
  - [ ] Ensure proper use of PHP 8+ features

- [ ] **Documentation**
  - [ ] Update any module-specific documentation
  - [ ] Add notes about attribute usage if needed
  - [ ] Update code comments referencing old annotations

## Testing Strategy

### Per-Module Testing Plan

#### Pre-Migration Tests
1. **Existing Test Suite**
   - Run all existing unit tests
   - Verify all tests pass before migration
   - Document any failing tests (should be fixed before migration)

2. **Database Schema**
   - Generate and save current schema
   - Verify schema generation works correctly
   - Test database migrations

3. **Entity Functionality**
   - Test entity creation, reading, updating, deletion
   - Verify relationship loading and persistence
   - Test repository methods and custom queries

#### Post-Migration Validation
1. **Functional Testing**
   - Re-run all existing tests (should pass unchanged)
   - Verify entity behavior is identical
   - Test complex query scenarios

2. **Integration Testing**
   - Test cross-module entity relationships
   - Validate cascading operations
   - Check entity event listeners

3. **Performance Testing**
   - Compare query performance before/after
   - Verify no performance regressions
   - Test with realistic data volumes

#### Cross-Module Testing
After each module migration:
- Test relationships with already-migrated modules
- Verify no issues with mixed annotation/attribute usage
- Test full application workflows involving multiple modules

## Implementation Timeline

### Recommended Schedule

#### Week 1: Foundation Modules
- **Days 1-2**: SlackIntegration module (1 entity)
- **Days 3-5**: ServiceLevel module (3 entities)
- **Day 5**: Integration testing and validation

#### Week 2: Site Management  
- **Days 1-3**: OrganisationSite module (2 entities)
- **Days 4-5**: OrganisationContact module (1 entity)
- **Day 5**: Cross-module integration testing

#### Week 3: Core Business Logic
- **Days 1-4**: Organisation module (2 entities)
- **Day 5**: Comprehensive testing with dependent modules

#### Week 4: Authentication System
- **Days 1-4**: User module (3 entities) - **High Priority Testing**
- **Day 5**: Security and authentication testing

#### Week 5: Ticket System
- **Days 1-5**: Ticket module (8 entities) - **Most Complex**
- Allow extra time for relationship validation

#### Week 6: Final Integration
- **Days 1-2**: Complete system integration testing
- **Days 3-4**: Performance testing and optimization
- **Day 5**: Documentation and cleanup

### Milestone Checkpoints
- **End of Week 1**: 33% entities migrated (low-risk modules complete)
- **End of Week 3**: 62% entities migrated (core business logic complete)
- **End of Week 5**: 100% entities migrated (all modules complete)
- **End of Week 6**: Full validation and production readiness

## Risk Mitigation

### Potential Issues and Solutions

#### Technical Risks
1. **Doctrine Proxy Cache Issues**
   - **Risk**: Cached proxies may conflict between annotations/attributes
   - **Solution**: Clear cache between each module migration
   - **Command**: `rm -rf data/cache/DoctrineEntityProxy/*`

2. **Complex Relationship Mappings**
   - **Risk**: Incorrect attribute syntax in complex relationships
   - **Solution**: Extra validation focus on Ticket module relationships
   - **Mitigation**: Test relationship loading thoroughly

3. **Repository Class References**
   - **Risk**: Inconsistent `::class` usage in entity definitions
   - **Solution**: Systematic search and replace with validation
   - **Check**: Ensure all repository references use proper syntax

4. **Type Hint Conflicts**
   - **Risk**: PHP 8+ type hints may conflict with existing code
   - **Solution**: Gradual introduction with backward compatibility
   - **Validation**: Comprehensive testing after each property update

#### Business Risks
1. **Authentication System Disruption**
   - **Risk**: User module migration could break authentication
   - **Solution**: Extensive security testing before/after migration
   - **Rollback**: Keep authentication working during migration

2. **Ticket System Downtime**
   - **Risk**: Complex Ticket module could cause service disruption  
   - **Solution**: Migrate during maintenance window with full testing
   - **Backup**: Complete database backup before Ticket module migration

3. **Data Integrity Issues**
   - **Risk**: Migration errors could corrupt entity relationships
   - **Solution**: Schema validation before/after each module
   - **Verification**: Compare database schemas for consistency

### Rollback Strategy

#### Per-Module Rollback
- **Git Branches**: Each module on separate feature branch
- **Database Schema**: Should remain unchanged (validation required)
- **Quick Rollback**: `git checkout main` to revert module changes
- **Cache Clearing**: May need to clear Doctrine cache after rollback

#### Emergency Rollback Plan
1. **Immediate**: Switch to previous git branch
2. **Cache**: Clear all Doctrine proxy classes
3. **Testing**: Run critical functionality tests
4. **Validation**: Verify system returns to pre-migration state

#### Rollback Testing
- Test rollback procedure for each module during development
- Ensure rollback doesn't require database changes
- Validate that partial rollbacks don't break cross-module relationships

## Success Criteria

### Technical Success Metrics
- [ ] All 21 entities successfully converted to attributes
- [ ] Zero annotation remnants in entity files (`grep -r "@ORM" src/` returns no results)
- [ ] All existing tests continue to pass
- [ ] Database schema generation identical before/after migration
- [ ] No performance regressions in entity operations
- [ ] Doctrine proxy classes generate correctly for all entities

### Functional Success Metrics
- [ ] Complete system functionality maintained
- [ ] User authentication and authorization working
- [ ] Ticket creation, management, and workflow intact
- [ ] Organisation and contact management functional
- [ ] Service level and SLA calculations correct
- [ ] All module integrations working properly

### Quality Success Metrics
- [ ] Code standards compliance maintained
- [ ] No breaking changes to public APIs
- [ ] Documentation updated where necessary
- [ ] Clean git history with logical commits per module
- [ ] Team knowledge transfer completed

## Post-Migration Activities

### Immediate Tasks
1. **Documentation Updates**
   - Update developer documentation to reference attributes
   - Create migration lessons learned document
   - Update coding standards to prefer attributes

2. **Team Knowledge Transfer**
   - Training session on PHP 8 attributes syntax
   - Review of new development patterns
   - Best practices documentation

3. **Monitoring**
   - Monitor application performance post-migration
   - Watch for any edge cases or issues
   - Gather feedback from development team

### Long-term Benefits
- **Modern PHP**: Leveraging PHP 8+ native features
- **Better IDE Support**: Enhanced autocompletion and validation
- **Improved Performance**: Potential performance benefits from native attributes
- **Code Clarity**: Cleaner, more readable entity definitions
- **Future-Proofing**: Aligned with Doctrine's recommended approach

## Conclusion

This comprehensive migration plan provides a systematic, low-risk approach to converting all Doctrine entities from annotations to attributes. The module-by-module strategy ensures project stability while enabling gradual validation and easy rollback capabilities.

The phased approach prioritizes low-risk modules first, building confidence and validating the migration process before tackling the more complex authentication and ticketing systems. With proper testing, validation, and rollback procedures, this migration can be completed successfully while maintaining full system functionality.

---

**Document Version**: 1.0  
**Last Updated**: 2025-01-29  
**Migration Status**: Planning Phase