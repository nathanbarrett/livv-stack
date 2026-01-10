import Color from 'color'

export function useColorContrast() {
  /**
   * Get a contrasting text color (black or white) for a given background color
   */
  function getContrastingTextColor(backgroundColor: string): string {
    try {
      const color = Color(backgroundColor)
      // Use luminosity to determine if we need light or dark text
      return color.isLight() ? '#000000' : '#ffffff'
    } catch {
      return '#000000'
    }
  }

  /**
   * Get a muted/secondary text color that contrasts well with the background
   * This is useful for timestamps, labels, etc.
   */
  function getMutedContrastColor(backgroundColor: string, opacity = 0.7): string {
    try {
      const color = Color(backgroundColor)
      const textColor = color.isLight() ? Color('#000000') : Color('#ffffff')
      return textColor.alpha(opacity).hexa()
    } catch {
      return 'rgba(0, 0, 0, 0.7)'
    }
  }

  /**
   * Parse a CSS variable value from the computed styles
   */
  function getCssVariableColor(variableName: string): string | null {
    if (typeof document === 'undefined') return null
    const style = getComputedStyle(document.documentElement)
    const value = style.getPropertyValue(variableName).trim()
    return value || null
  }

  /**
   * Convert Vuetify RGB variable format "R,G,B" or "R G B" to a Color-compatible format
   */
  function vuetifyRgbToColor(rgbString: string): string {
    // Handle both comma-separated and space-separated formats
    const parts = rgbString.trim().split(/[\s,]+/)
    if (parts.length === 3) {
      return `rgb(${parts[0]}, ${parts[1]}, ${parts[2]})`
    }
    return rgbString
  }

  return {
    getContrastingTextColor,
    getMutedContrastColor,
    getCssVariableColor,
    vuetifyRgbToColor,
  }
}
