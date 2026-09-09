import { ChevronDown, ChevronUp } from 'lucide-react';

import { cn } from '@/lib/utils';

export default function SortableTh({ column, sort, direction, onSort }) {
  const active = sort === column.key;

  return (
    <th className="px-6 py-3 text-left">
      <button
        type="button"
        onClick={() => onSort(column.key)}
        className={cn(
          'inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider transition hover:text-foreground',
          active ? 'text-foreground' : 'text-muted-foreground',
        )}
      >
        {column.label}
        {active &&
          (direction === 'asc' ? (
            <ChevronUp className="size-3" />
          ) : (
            <ChevronDown className="size-3" />
          ))}
      </button>
    </th>
  );
}