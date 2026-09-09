import { Head, Link, router } from "@inertiajs/react";
import { ArrowDown, ArrowUp, CalendarDays, Search, X } from "lucide-react";
import { useEffect, useState } from "react";

import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { cn } from "@/lib/utils";

const ATTENDANCE_FILTERS = ["attending", "declined"];

const SORT_ITEMS = {
  couple_name: "Couple name",
  event_date: "Event date",
  responses_count: "Responses",
  created_at: "Created",
};

const formatDate = (value) => {
  if (!value) return "—";
  return new Date(value).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const responseColumns = [
  "Guest",
  "Attending",
  "Guests",
  "Wish message",
  "Hidden",
  "Submitted",
];

export default function Index({ invitations, filters, attendanceCounts }) {
  const [search, setSearch] = useState(filters.search ?? "");
  const hasFilters = Boolean(filters.attending) || Boolean(filters.search);

  useEffect(() => {
    const timeout = setTimeout(() => {
      if (search === filters.search) return;

      router.get(
        route("admin.responses.index"),
        {
          ...(filters.attending ? { attending: filters.attending } : {}),
          ...(filters.sort ? { sort: filters.sort } : {}),
          ...(filters.direction ? { direction: filters.direction } : {}),
          ...(search ? { search } : {}),
        },
        {
          preserveScroll: true,
          preserveState: true,
          only: ["invitations", "filters"],
          replace: true,
        },
      );
    }, 400);

    return () => clearTimeout(timeout);
  }, [search]);

  const tabHref = (attending) => {
    const params = {};
    if (attending) params.attending = attending;
    if (filters.search) params.search = filters.search;
    if (filters.sort) params.sort = filters.sort;
    if (filters.direction) params.direction = filters.direction;
    return route("admin.responses.index", params);
  };

  const reload = (params) => {
    router.get(
      route("admin.responses.index"),
      {
        ...(filters.attending ? { attending: filters.attending } : {}),
        ...(filters.search ? { search: filters.search } : {}),
        ...params,
      },
      {
        preserveScroll: true,
        preserveState: true,
        only: ["invitations", "filters"],
      },
    );
  };

  const changeSort = (sort) => reload({ sort, direction: filters.direction ?? "asc" });

  const toggleDirection = () => {
    const direction = filters.direction === "asc" ? "desc" : "asc";
    reload({ sort: filters.sort ?? "created_at", direction });
  };

  const tabs = [
    { key: "", label: "All", count: attendanceCounts?.total },
    { key: "attending", label: "Attending", count: attendanceCounts?.attending },
    { key: "declined", label: "Declined", count: attendanceCounts?.declined },
  ];

  return (
    <>
      <Head title="Admin Responses" />

      <AdminLayout>
        <div className="mb-6">
          <h1 className="font-display text-2xl font-bold tracking-tight text-foreground">
            Responses
          </h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Guest responses grouped by invitation.
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
                  (!filters.attending && tab.key === "") ||
                    filters.attending === tab.key
                    ? "bg-primary text-primary-foreground"
                    : "bg-card text-muted-foreground ring-1 ring-border hover:bg-muted",
                )}
              >
                {tab.label}
                <span
                  className={cn(
                    "rounded-full px-2 py-0.5 text-xs tabular-nums",
                    (!filters.attending && tab.key === "") ||
                      filters.attending === tab.key
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
              <Link href={route("admin.responses.index")}>
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
                placeholder="Search guest, wish message..."
                className="w-64 pl-9"
              />
            </div>
          </div>
        </div>

        <div className="mb-4 flex items-center gap-2">
          <span className="text-sm font-medium text-foreground">Sort by</span>
          <Select
            items={SORT_ITEMS}
            value={filters.sort ?? "created_at"}
            onValueChange={changeSort}
          >
            <SelectTrigger className="h-8 w-44 text-sm">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {Object.entries(SORT_ITEMS).map(([key, label]) => (
                <SelectItem key={key} value={key}>
                  {label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Button
            variant="ghost"
            size="sm"
            onClick={toggleDirection}
            className="text-muted-foreground"
            aria-label="Toggle sort direction"
          >
            {filters.direction === "asc" ? (
              <ArrowUp className="size-4" />
            ) : (
              <ArrowDown className="size-4" />
            )}
          </Button>
        </div>

        {invitations.data.length === 0 ? (
          <Card>
            <CardContent className="py-16 text-center">
              <p className="text-sm font-medium text-foreground">
                No responses found
              </p>
              <p className="mt-1 text-sm text-muted-foreground">
                Try adjusting your filters or search.
              </p>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {invitations.data.map((invitation) => (
              <div
                key={invitation.id}
                className="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border"
              >
                <div className="flex flex-col gap-3 border-b border-border bg-muted/50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <a
                      href={`/${invitation.slug}`}
                      target="_blank"
                      rel="noreferrer"
                      className="text-sm font-medium text-foreground transition hover:text-primary"
                    >
                      {invitation.groom_name} & {invitation.bride_name}
                    </a>
                    <p className="mt-0.5 truncate text-xs text-muted-foreground">
                      {invitation.user?.email}
                    </p>
                  </div>

                  <div className="flex flex-wrap items-center gap-2">
                    <span className="inline-flex items-center gap-1 text-xs text-muted-foreground">
                      <CalendarDays className="size-3.5" />
                      {formatDate(invitation.event_date)}
                    </span>
                    {[
                      { label: "Responses", value: invitation.responses_count },
                      { label: "Attending", value: invitation.attending_count },
                      { label: "Guests", value: invitation.guests_count },
                      { label: "Wishes", value: invitation.wishes_count },
                    ].map(({ label, value }) => (
                      <span
                        key={label}
                        className="inline-flex items-center gap-1 rounded-full bg-card px-2 py-0.5 text-xs font-medium tabular-nums text-muted-foreground ring-1 ring-border"
                      >
                        {value ?? 0}
                        <span className="font-normal text-muted-foreground">
                          {label}
                        </span>
                      </span>
                    ))}
                  </div>
                </div>

                {invitation.responses.length === 0 ? (
                  <p className="px-6 py-8 text-center text-sm text-muted-foreground">
                    No matching responses for this invitation.
                  </p>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-border">
                      <thead className="bg-card">
                        <tr>
                          {responseColumns.map((label) => (
                            <th
                              key={label}
                              className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground"
                            >
                              {label}
                            </th>
                          ))}
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-border bg-card">
                        {invitation.responses.map((response) => (
                          <tr
                            key={response.id}
                            className="transition hover:bg-muted"
                          >
                            <td className="px-6 py-4">
                              <p className="text-sm font-medium text-foreground">
                                {response.guest_name}
                              </p>
                              <p className="mt-0.5 text-xs text-muted-foreground">
                                <span className="tabular-nums">#{response.id}</span>
                              </p>
                            </td>
                            <td className="px-6 py-4">
                              <Badge variant="outline" className="rounded-full">
                                <span
                                  className={cn(
                                    "size-2 shrink-0 rounded-full",
                                    response.is_attending
                                      ? "bg-emerald-500"
                                      : "bg-red-500",
                                  )}
                                />
                                {response.is_attending ? "Accepted" : "Declined"}
                              </Badge>
                            </td>
                            <td className="px-6 py-4">
                              <span className="inline-flex min-w-8 items-center justify-center rounded-full bg-muted px-2 py-0.5 text-xs font-medium tabular-nums text-foreground">
                                {response.count}
                              </span>
                            </td>
                            <td className="max-w-md px-6 py-4">
                              {response.message ? (
                                <p className="line-clamp-2 text-sm text-muted-foreground">
                                  {response.message}
                                </p>
                              ) : (
                                <span className="text-sm text-muted-foreground">—</span>
                              )}
                            </td>
                            <td className="px-6 py-4">
                              {response.is_hidden ? (
                                <span className="inline-flex items-center rounded-full bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground">
                                  Hidden
                                </span>
                              ) : (
                                <span className="text-sm text-muted-foreground">—</span>
                              )}
                            </td>
                            <td className="px-6 py-4 text-sm text-muted-foreground">
                              {formatDate(response.created_at)}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            ))}

            <Pagination pagination={invitations} />
          </div>
        )}
      </AdminLayout>
    </>
  );
}