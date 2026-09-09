import { useState } from 'react';
import { Eye, X } from 'lucide-react';
import { cn } from '@/lib/utils';

export default function PreviewBanner({ preview, status }) {
  const [dismissed, setDismissed] = useState(false);

  if (!preview || dismissed) return null;

  const label = status === 'inactive' ? 'Inactive' : 'Pending';

  return (
    <div
      role="status"
      className={cn(
        'fixed inset-x-0 top-0 z-[100] flex items-center gap-3 border-b border-white/10',
        'bg-slate-900/95 px-4 py-2.5 text-sm text-white backdrop-blur',
      )}
    >
      <Eye className="size-4 shrink-0 text-amber-400" />
      <p className="min-w-0 flex-1">
        You&apos;re previewing this {label} invitation — guests can only see it once
        it&apos;s active.
      </p>
      <button
        type="button"
        onClick={() => setDismissed(true)}
        className="shrink-0 text-white/70 transition hover:text-white"
        aria-label="Dismiss preview banner"
      >
        <X className="size-4" />
      </button>
    </div>
  );
}