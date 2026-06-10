/** Whether notice text indicates cancelled traffic (mirrors PHP MRT_notice_indicates_cancelled). */
export function isCancelledNotice(notice: string): boolean {
  const text = notice.trim().toLowerCase();
  if (!text) {
    return false;
  }
  return text.includes('inställd') || text.includes('installd');
}

export function connectionIsCancelled(
  connection: { is_cancelled?: boolean; notice?: string },
): boolean {
  if (connection.is_cancelled) {
    return true;
  }
  return isCancelledNotice(connection.notice || '');
}
