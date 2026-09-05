import { Link } from '@inertiajs/react';
import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-react';

import { cn } from '@/lib/utils';

function PageButton({ href, active, disabled, children, ...props }) {
  if (disabled) {
    return (
      <span
        className={cn(
          'inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-gray-200 px-3 text-sm font-medium text-gray-300',
          props?.className,
        )}
      >
        {children}
      </span>
    );
  }

  return (
    <Link
      href={href}
      preserveScroll
      className={cn(
        'inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm font-medium transition',
        active
          ? 'border-primary bg-primary text-primary-foreground'
          : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50',
        props?.className,
      )}
    >
      {children}
    </Link>
  );
}

export default function Pagination({ pagination }) {
  const links = pagination?.links ?? [];
  const meta = {
    currentPage: pagination?.current_page,
    lastPage: pagination?.last_page,
    from: pagination?.from,
    to: pagination?.to,
    total: pagination?.total,
  };

  const prev = links[0];
  const next = links[links.length - 1];
  const pages = links.slice(1, -1);

  return (
    <div className="flex flex-col items-center justify-between gap-4 border-t border-gray-200 px-4 py-3 sm:flex-row sm:px-6">
      <p className="text-sm text-gray-500">
        Showing <span className="font-medium">{meta.from ?? 0}</span> to{' '}
        <span className="font-medium">{meta.to ?? 0}</span> of{' '}
        <span className="font-medium">{meta.total ?? 0}</span> results
      </p>

      <nav className="flex items-center gap-1">
        <PageButton href={prev?.url} disabled={!prev?.url} aria-label="Previous page">
          <ChevronLeftIcon className="size-4" />
        </PageButton>

        {pages.map((page) => (
          <PageButton
            key={page.label}
            href={page.url}
            active={page.active}
            disabled={!page.url}
          >
            {page.label}
          </PageButton>
        ))}

        <PageButton href={next?.url} disabled={!next?.url} aria-label="Next page">
          <ChevronRightIcon className="size-4" />
        </PageButton>
      </nav>
    </div>
  );
}