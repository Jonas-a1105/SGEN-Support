// ==============================================================================
// SGEN-Support - Commitlint Configuration (Conventional Commits)
// ==============================================================================

module.exports = {
    extends: ['@commitlint/config-conventional'],
    rules: {
        'type-enum': [
            2,
            'always',
            [
                'feat',     // New feature for the user
                'fix',      // Bug fix for the user
                'docs',     // Changes to documentation
                'style',    // Formatting, missing semicolons, etc. (no code change)
                'refactor', // Refactoring production code (neither fixes a bug nor adds a feature)
                'perf',     // Code change that improves performance
                'test',     // Adding missing tests or refactoring tests
                'build',    // Changes that affect the build system or external dependencies
                'ci',       // Changes to CI configuration files and scripts
                'chore',    // Other changes that don't modify src or test files
                'revert'    // Reverts a previous commit
            ]
        ],
        'subject-case': [2, 'never', ['sentence-case', 'start-case', 'pascal-case', 'upper-case']],
        'subject-empty': [2, 'never'],
        'subject-full-stop': [2, 'never', '.'],
        'type-case': [2, 'always', 'lower-case'],
        'type-empty': [2, 'never'],
        'scope-case': [2, 'always', 'lower-case']
    }
};
