Use the firewall mcp tool to create documentation for a project based on the following rules:
Use firecrawl to scrape / crawl url $ARGUMENTS and extract all relevant documentation about the project, including but not limited to:
- Project overview
- All sections and subsections (if any)

Output format:
- Add every section as markdown files in the ./docs/{project-name} folder
- If there are many sections in the documentation, split them into multiple markdown files with logical names with the following structure
  - ./docs/{project-name}/INDEX.md
    - ./docs/{project-name}/sections/{SUBSECTION_NAME}.md
- If there are only a few sections, you can put everything in the INDEX.md file directly without creating a sections folder.
- The INDEX.md file should contain a paragraph description of the project, a link to the docs url, and the main table of contents and link to all other section files. The table of contents should provide a brief description of each section.
- Each section file should contain the relevant documentation for that section only.
- Ensure that the documentation is clear, concise, and well-organized for easy navigation.
