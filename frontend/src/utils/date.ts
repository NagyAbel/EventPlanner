// src/utils/date.ts
export const parseDate = (dateStr?: string | null): Date | null => {
  if (!dateStr) return null
  const normalized = dateStr.includes(' ') ? dateStr.replace(' ', 'T') : dateStr
  const dateObj = new Date(normalized)
  return isNaN(dateObj.getTime()) ? null : dateObj
}

export const formatDate = (dateStr?: string | null, locale = 'en-US'): string | null => {
  const dateObj = parseDate(dateStr)
  if (!dateObj) return null
  return new Intl.DateTimeFormat(locale, {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  }).format(dateObj)
}

export const formatTime = (dateStr?: string | null, locale = 'en-US'): string => {
  const dateObj = parseDate(dateStr)
  if (!dateObj) return ''
  return new Intl.DateTimeFormat(locale, {
    hour: 'numeric',
    minute: '2-digit',
  }).format(dateObj)
}