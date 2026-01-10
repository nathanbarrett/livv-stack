import { marked, Renderer, MarkedOptions } from 'marked'
import hljs from 'highlight.js'

// Configure marked with syntax highlighting
const renderer = new Renderer()

// Override code block rendering with highlight.js
renderer.code = function ({ text, lang }: { text: string; lang?: string; escaped?: boolean }) {
  const language = lang && hljs.getLanguage(lang) ? lang : 'plaintext'
  const highlighted = hljs.highlight(text, { language }).value
  return `<pre class="hljs"><code class="language-${language}">${highlighted}</code></pre>`
}

const markedOptions: MarkedOptions = {
  renderer,
  gfm: true,
  breaks: true,
}

marked.setOptions(markedOptions)

export function useMarkdown() {
  function renderMarkdown(content: string): string {
    if (!content) return ''
    return marked.parse(content) as string
  }

  return { renderMarkdown }
}
