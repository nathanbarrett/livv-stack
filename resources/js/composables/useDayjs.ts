import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import localizedFormat from 'dayjs/plugin/localizedFormat'

dayjs.extend(relativeTime)
dayjs.extend(localizedFormat)

export function useDayjs() {
  const formatDateTime = (date: string): string => {
    return dayjs(date).format('MMM D, YYYY h:mm A')
  }

  const formatRelative = (date: string): string => {
    return dayjs(date).fromNow()
  }

  const formatDate = (date: string): string => {
    return dayjs(date).format('MMM D, YYYY')
  }

  const formatTime = (date: string): string => {
    return dayjs(date).format('h:mm A')
  }

  return {
    dayjs,
    formatDateTime,
    formatRelative,
    formatDate,
    formatTime,
  }
}
