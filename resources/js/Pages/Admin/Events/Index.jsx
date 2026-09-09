import { Head, Link, router } from "@inertiajs/react";
import { Search, X } from "lucide-react";
import { useEffect, useState } from "react";

import Pagination from "@/Components/Pagination";
import SortableTh from "@/Components/SortableTh";
import AdminLayout from "@/Layouts/AdminLayout";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";

const USAGE_FILTERS = ["in-use", "unused"];

const formatDate = (value) => {
  if (!value) return "—";
  return new Date(value).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const columns = [
  { key: "name", label: "Event" },
  { key: "sort_order", label: "Order" },
  { key: "invitations_count", label: "Invitations" },
  { key: "created_at", label: "Created" },
];

export default function Index({ events, filters, usageCounts }) {
  const [search, setSearch] = useState(filters.search ?? "");

  const hasFilters = Boolean(filters.usage) || Boolean(filters.search);

  useEffect(() => {
    const timeout = setTimeout(() => {
      if (search === filters.search) return;

      router.get(
        route("admin.events.index"),
        {
          ...(filters.usage ? { usage: filters.usage } : {}),
          ...(filters.sort ? { sort: filters.sort } : {}),
          ...(filters.direction ? { direction: filters.direction } : {}),
          ...(search ? { search } : {}),
        },
        {
          preserveScroll: true,
          preserveState: true,
          only: ["events", "filters"],
          replace: true,
        },
      );
    }, 400);

    return () => clearTimeout(timeout);
  }, [search]);

  const tabHref = (usage) => {
    const params = {};
    if (usage) params.usage = usage;
    if (filters.search) params.search = filters.search;
    if (filters.sort) params.sort = filters.sort;
    if (filters.direction) params.direction = filters.direction;
    return route("admin.events.index", params);
  };

  const handleSort = (key) => {
    const nextDirection =
      filters.sort === key && filters.direction === "asc" ? "desc" : "asc";

    router.get(
      route("admin.events.index"),
      {
        ...(filters.usage ? { usage: filters.usage } : {}),
        ...(filters.search ? { search: filters.search } : {}),
        sort: key,
        direction: nextDirection,
      },
      {
        preserveScroll: true,
        preserveState: true,
        only: ["events", "filters"],
      },
    );
  };

  const tabs = [
    { key: "", label: "All", count: usageCounts?.total },
    { key: "in-use", label: "In use", count: usageCounts?.inUse },
    { key: "unused", label: "Unused", count: usageCounts?.unused },
  ];

  return (
    <>
      <Head title="Admin Events" />

      <AdminLayout>
        <div className="mb-6">
          <h1 className="font-display text-2xl font-bold tracking-tight text-foreground">
            Events
          </h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Every timeline event in the catalog and how it is being used.
          </p>
        </div>

        <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex flex-wrap items-center gap-2">
            {tabs.map((tab) => (
              <Link
                key={tab.key}
                href={tabHref(tab.key)}
                className={cn(
                  "inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition",
                  (!filters.usage && tab.key === "") ||
                    filters.usage === tab.key
                    ? "bg-primary text-primary-foreground"
                    : "bg-card text-muted-foreground ring-1 ring-border hover:bg-muted",
                )}
              >
                {tab.label}
                <span
                  className={cn(
                    "rounded-full px-2 py-0.5 text-xs tabular-nums",
                    (!filters.usage && tab.key === "") ||
                      filters.usage === tab.key
                      ? "bg-white/20 text-white"
                      : "bg-muted text-muted-foreground",
                  )}
                >
                  {tab.count ?? 0}
                </span>
              </Link>
            ))}
          </div>

          <div className="flex items-center gap-2">
            {hasFilters && (
              <Link href={route("admin.events.index")}>
                <Button variant="ghost" size="sm">
                  <X /> Reset
                </Button>
              </Link>
            )}

            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                type="search"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search event name..."
                className="w-64 pl-9"
              />
            </div>
          </div>
        </div>

        <div className="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border">
          {events.data.length === 0 ? (
            <Card>
              <CardContent className="py-16 text-center">
                <p className="text-sm font-medium text-foreground">
                  No events found
                </p>
                <p className="mt-1 text-sm text-muted-foreground">
                  Try adjusting your filters or search.
                </p>
              </CardContent>
            </Card>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-border">
                <thead className="bg-muted/50">
                  <tr>
                    {columns.map((column) => (
                      <SortableTh
                        key={column.key}
                        column={column}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={handleSort}
                      />
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-border bg-card">
                  {events.data.map((event) => (
                    <tr key={event.id} className="transition hover:bg-muted">
                      <td className="px-6 py-4">
                        <p className="text-sm font-medium text-foreground">
                          {event.name}
                        </p>
                        <p className="mt-0.5 text-xs text-muted-foreground">
                          <span className="tabular-nums">#{event.id}</span>
                        </p>
                      </td>
                      <td className="px-6 py-4">
                        <span className="inline-flex min-w-8 items-center justify-center rounded-full bg-muted px-2 py-0.5 text-xs font-medium tabular-nums text-foreground">
                          {event.sort_order}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="inline-flex min-w-8 items-center justify-center rounded-full bg-muted px-2 py-0.5 text-xs font-medium tabular-nums text-foreground">
                          {event.invitations_count}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-sm text-muted-foreground">
                        {formatDate(event.created_at)}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {events.data.length > 0 && <Pagination pagination={events} />}
        </div>
      </AdminLayout>
    </>
  );
}