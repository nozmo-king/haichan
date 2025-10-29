export async function createPost(
  threadId: number,
  body: string | null,
  imageFile?: File,
  parentId?: number
) {
  const fd = new FormData();
  if (body) fd.set('body', body);
  if (parentId !== undefined) fd.set('parent_id', String(parentId));
  if (imageFile) fd.set('image', imageFile);

  const res = await fetch(`/api/threads/${threadId}/posts`, {
    method: 'POST',
    body: fd,
    credentials: 'include'
  });
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}

export async function fetchThreadPosts(threadId: number) {
  const res = await fetch(`/api/threads/${threadId}/posts`, { credentials: 'include' });
  if (!res.ok) throw new Error(await res.text());
  return res.json() as Promise<{data: Array<{
    id: number; parent_id: number|null; body: string|null; image_path: string|null; created_at: string;
  }>}>;
}