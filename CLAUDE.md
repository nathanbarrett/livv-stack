# App Overview
Put app overview here.

# Tech Stack And Best Practices
IMPORTANT GENERAL NOTES:
- for any `sail ...` commands given, if they don't work because sail is not recognized, prefix them with `./vendor/bin/` i.e. `./vendor/bin/sail ...`
- Always assume that `npm run dev` is already running somewhere
- For a general overview of how this app works see `docs/app/INDEX.md`
- If you intend to interface with AI services adhere to the following:
  - services always default to using the Laravel Prism library unless specifically told not to use it, read `docs/prism/INDEX.md` for more info.
  - For any AI provider you use, always check AI provider specific documentation in Prism docs to see if there are any special instructions for that provider.
  - Prompts classes are stored in `app/AI/Prompts/`
  - AI Prism tools are stored in `app/AI/Tools/`
  - use dynamic context mcp for additional context

# Troubleshooting
- All outgoing HTTP requests made by the backend (via Laravel's HTTP client) are logged in the `http_logs` table. Use this for troubleshooting API calls, debugging external service integrations, and inspecting request/response payloads. The dashboard is available at `/spy` when authenticated.

<!-- DYNAMIC CONTEXT MCP GUIDELINES START -->

<CRITICAL_INSTRUCTION>

## DYNAMIC CONTEXT INSTRUCTIONS

This project uses dynamic context mcp to deliver "just in time" context for files that you are about to read, create, or edit.

**CRITICAL GUIDANCE**

- dynamic context mcp takes a file path as input and goes through all context files that match that path (by glob patterns) to compile relevant context for that file
- Before you read, create, or edit a file, check for dynamic context by requesting the `dynamic-context.get_context_for_file` tool with the file path as input.
- If multiple files have the same path and extension then assume that the compiled context is the same for all of them and use the same context for all of them.
- If dynamic context is available, read it carefully to understand important details about how to work with that file.

</CRITICAL_INSTRUCTION>

<!-- DYNAMIC CONTEXT MCP GUIDELINES END -->

<!-- BACKLOG.MD MCP GUIDELINES START -->

<CRITICAL_INSTRUCTION>

## BACKLOG WORKFLOW INSTRUCTIONS

This project uses Backlog.md MCP for all task and project management activities.

**CRITICAL GUIDANCE**

- If your client supports MCP resources, read `backlog://workflow/overview` to understand when and how to use Backlog for this project.
- If your client only supports tools or the above request fails, call `backlog.get_workflow_overview()` tool to load the tool-oriented overview (it lists the matching guide tools).

- **First time working here?** Read the overview resource IMMEDIATELY to learn the workflow
- **Already familiar?** You should have the overview cached ("## Backlog.md Overview (MCP)")
- **When to read it**: BEFORE creating tasks, or when you're unsure whether to track work

These guides cover:
- Decision framework for when to create tasks
- Search-first workflow to avoid duplicates
- Links to detailed guides for task creation, execution, and completion
- MCP tools reference

You MUST read the overview resource to understand the complete workflow. The information is NOT summarized here.

</CRITICAL_INSTRUCTION>

<!-- BACKLOG.MD MCP GUIDELINES END -->
