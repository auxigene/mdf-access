# Processes Documentation

This folder contains numbered use case processes for the MDF Access platform. These processes provide step-by-step instructions for common operational tasks, especially those needed during development.

## Purpose

- Document standard operational procedures
- Provide quick reference guides for developers
- Ensure consistency across the team
- Facilitate onboarding of new team members

## Process Index

### Development Processes

- **[000-add-organization-without-ui.md](000-add-organization-without-ui.md)** - Add a new organization without having access to user interfaces. Essential during development stage when UI is unavailable or not yet implemented. Must be done before creating users.
- **[001-add-user-without-ui.md](001-add-user-without-ui.md)** - Add a new user without having access to user interfaces. Essential during development stage when UI is unavailable or not yet implemented.

## Naming Convention

Processes are numbered sequentially using the format:
```
NNN-descriptive-name.md
```

Where:
- `NNN` = Three-digit process number (001, 002, 003, etc.)
- `descriptive-name` = Kebab-case description of the process
- `.md` = Markdown file extension

## Contributing

When adding a new process:

1. Use the next available number
2. Follow the existing template structure
3. Include practical examples and code snippets
4. Add troubleshooting section
5. Update this README with the new process
6. Keep it concise and actionable

## Process Template Structure

Each process document should include:

1. **Title** - Clear process name with number
2. **Purpose** - Why this process exists
3. **Use Cases** - When to use this process
4. **Methods** - Step-by-step instructions (multiple approaches if applicable)
5. **Reference Tables** - Quick lookup information
6. **Troubleshooting** - Common issues and solutions
7. **Security Notes** - Important security considerations
8. **Quick Reference** - One-line commands for experienced users

---

**Last Updated:** 2025-11-20
