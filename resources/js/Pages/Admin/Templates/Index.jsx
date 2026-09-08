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
  { key: "name", label: "Template", sortable: true },
  { key: "description", label: "Description" },
  { key: "invitations_count", label: "Invitations", sortable: true },
  { key: "created_at", label: "Created", sortable: true },
];

export default function Index({ templates, filters, usageCounts }) {
  const [search, setSearch] = useState(filters.search ?? "");
  const hasFilters = Boolean(filters.usage) || Boolean(filters.search);

  useEffect(() => {
    const timeout = setTimeout(() => {
      if (search === filters.search) return;

      router.get(
        route("admin.templates.index"),
        {
          ...(filters.usage ? { usage: filters.usage } : {}),
          ...(filters.sort ? { sort: filters.sort } : {}),
          ...(filters.direction ? { direction: filters.direction } : {}),
          ...(search ? { search } : {}),
        },
        {
          preserveScroll: true,
          preserveState: true,
          only: ["templates", "filters"],
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
    return route("admin.templates.index", params);
  };

  const handleSort = (key) => {
    const nextDirection =
      filters.sort === key && filters.direction === "asc" ? "desc" : "asc";

    router.get(
      route("admin.templates.index"),
      {
        ...(filters.usage ? { usage: filters.usage } : {}),
        ...(filters.search ? { search: filters.search } : {}),
        sort: key,
        direction: nextDirection,
      },
      {
        preserveScroll: true,
        preserveState: true,
        only: ["templates", "filters"],
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
      <Head title="Admin Templates" />

      <AdminLayout>
        <div className="mb-6">
          <h1 className="text-2xl font-bold tracking-tight text-gray-900">
            Templates
          </h1>
          <p className="mt-1 text-sm text-gray-500">
            Manage every template and how it is being used.
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
                    ? "bg-gray-900 text-white"
                    : "bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50",
                )}
              >
                {tab.label}
                <span
                  className={cn(
                    "rounded-full px-2 py-0.5 text-xs tabular-nums",
                    (!filters.usage && tab.key === "") ||
                      filters.usage === tab.key
                      ? "bg-white/20 text-white"
                      : "bg-gray-100 text-gray-500",
                  )}
                >
                  {tab.count ?? 0}
                </span>
              </Link>
            ))}
          </div>

          <div className="flex items-center gap-2">
            {hasFilters && (
              <Link href={route("admin.templates.index")}>
                <Button variant="ghost" size="sm">
                  <X /> Reset
                </Button>
              </Link>
            )}

            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
              <Input
                type="search"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search name, slug, description..."
                className="w-64 pl-9"
              />
            </div>
          </div>
        </div>

        <div className="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
          {templates.data.length === 0 ? (
            <Card>
              <CardContent className="py-16 text-center">
                <p className="text-sm font-medium text-gray-900">
                  No templates found
                </p>
                <p className="mt-1 text-sm text-gray-500">
                  Try adjusting your filters or search.
                </p>
              </CardContent>
            </Card>
          ) : (
            <>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      {columns.map((column) =>
                        column.sortable ? (
                          <SortableTh
                            key={column.key}
                            column={column}
                            sort={filters.sort}
                            direction={filters.direction}
                            onSort={handleSort}
                          />
                        ) : (
                          <th
                            key={column.key}
                            className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                          >
                            {column.label}
                          </th>
                        ),
                      )}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-200 bg-white">
                    {templates.data.map((template) => (
                      <tr
                        key={template.id}
                        className="transition hover:bg-gray-50"
                      >
                        <td className="px-6 py-4">
                          <div className="flex items-center gap-3">
                          <img
                            src={`/${template.thumbnail_path}`}
                            alt={template.name}
                            className="h-16 w-12 shrink-0 rounded-md object-cover ring-1 ring-gray-200"
                          />
                          <div>
                            <p className="text-sm font-medium text-gray-900">
                              {template.name}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                              <span className="tabular-nums">#{template.id}</span>
                              {` · ${template.slug}`}
                            </p>
                          </div>
                        </div>
                      </td>
                      <td className="max-w-sm px-6 py-4">
                        <p className="line-clamp-2 text-sm text-gray-600">
                          {template.description || "—"}
                        </p>
                      </td>
                      <td className="px-6 py-4">
                        <span className="inline-flex min-w-8 items-center justify-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium tabular-nums text-gray-700">
                          {template.invitations_count}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-500">
                        {formatDate(template.created_at)}
                      </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              <Pagination pagination={templates} />
            </>
          )}
        </div>
      </AdminLayout>
    </>
  );
}