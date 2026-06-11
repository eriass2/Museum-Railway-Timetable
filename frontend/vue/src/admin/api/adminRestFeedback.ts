import { adminFetch } from './adminRestCore';

export type FeedbackStatus = 'new' | 'read' | 'resolved' | 'dismissed';

export type FeedbackItem = {
  id: number;
  title: string;
  message: string;
  type: 'bug' | 'suggestion';
  email: string;
  page_url: string;
  wizard_step: string;
  status: FeedbackStatus;
  context: Record<string, string | number>;
};

export function listFeedback() {
  return adminFetch<{ items: FeedbackItem[] }>('/feedback');
}

export function updateFeedbackStatus(id: number, status: FeedbackStatus) {
  return adminFetch<FeedbackItem>(`/feedback/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ status }),
  });
}
