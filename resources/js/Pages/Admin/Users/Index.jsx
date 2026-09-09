import { Head, Link, router } from "@inertiajs/react";
import { Mail, Search, X } from "lucide-react";
import { useEffect, useState } from "react";

import Pagination from "@/Components/Pagination";
import SortableTh from "@/Components/SortableTh";
import AdminLayout from "@/Layouts/AdminLayout";
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

const ROLES = ["admin", "customer"];

const ROLE_ITEMS = {
  admin: "Admin",
  customer: "Customer",
};

const formatDate = (value) => {
  if (!value) return "—";
  return new Date(value).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const columns = [
  { key: "name", label: "User" },
  { key: "email", label: "Email" },
  { key: "role", label: "Role" },
  { key: "invitations_count", label: "Invitations" },
  { key: "created_at", label: "Joined" },
];

export default function Index({ users, filters, roleCounts }) {
  const [search, setSearch] = useState(filters.search ?? "");
  const hasFilters =
    Boolean(filters.role) || Boolean(filters.search);

  useEffect(() => {
    const timeout = setTimeout(() => {
      if (search === filters.search) return;

      router.get(
        route("admin.users.index"),
        {
          ...(filters.role ? { role: filters.role } : {}),
          ...(filters.sort ? { sort: filters.sort } : {}),
          ...(filters.direction ? { direction: filters.direction } : {}),
          ...(search ? { search } : {}),
        },
        {
          preserveScroll: true,
          preserveState: true,
          only: ["users", "filters"],
          replace: true,
        },
      );
    }, 400);

    return () => clearTimeout(timeout);
  }, [search]);

  const tabHref = (tab) => {
    const params = {};
    if (tab.key === "admin" || tab.key === "customer") params.role = tab.key;
    if (filters.search) params.search = filters.search;
    if (filters.sort) params.sort = filters.sort;
    if (filters.direction) params.direction = filters.direction;
    return route("admin.users.index", params);
  };

  const handleSort = (key) => {
    const nextDirection =
      filters.sort === key && filters.direction === "asc" ? "desc" : "asc";

    router.get(
      route("admin.users.index"),
      {
        ...(filters.role ? { role: filters.role } : {}),
        ...(filters.search ? { search: filters.search } : {}),
        sort: key,
        direction: nextDirection,
      },
      {
        preserveScroll: true,
        preserveState: true,
        only: ["users", "filters"],
      },
    );
  };

  const updateRole = (user, role) => {
    router.patch(
      route("admin.users.role", user.id),
      { role },
      {
        preserveScroll: true,
        only: ["users", "filters", "roleCounts", "flash"],
      },
    );
  };

  const tabs = [
    { key: "", label: "All", count: roleCounts?.total },
    { key: "admin", label: "Admins", count: roleCounts?.admin },
    { key: "customer", label: "Customers", count: roleCounts?.customer },
  ];

  const isTabActive = (tab) => {
    if (tab.key === "") return !filters.role;
    return filters.role === tab.key;
  };

  return (
    <>
      <Head title="Admin Users" />

      <AdminLayout>
        <div className="mb-6">
          <h1 className="font-display text-2xl font-bold tracking-tight text-foreground">
            Users
          </h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Manage every user and their activity.
          </p>
        </div>

        <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex flex-wrap items-center gap-2">
            {tabs.map((tab) => (
              <Link
                key={tab.key}
                href={tabHref(tab)}
                className={cn(
                  "inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition",
                  isTabActive(tab)
                    ? "bg-primary text-primary-foreground"
                    : "bg-card text-muted-foreground ring-1 ring-border hover:bg-muted",
                )}
              >
                {tab.label}
                <span
                  className={cn(
                    "rounded-full px-2 py-0.5 text-xs tabular-nums",
                    isTabActive(tab)
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
              <Link href={route("admin.users.index")}>
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
                placeholder="Search name, email..."
                className="w-64 pl-9"
              />
            </div>
          </div>
        </div>

        <div className="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border">
          {users.data.length === 0 ? (
            <Card>
              <CardContent className="py-16 text-center">
                <p className="text-sm font-medium text-foreground">
                  No users found
                </p>
                <p className="mt-1 text-sm text-muted-foreground">
                  Try adjusting your filters or search.
                </p>
              </CardContent>
            </Card>
          ) : (
            <>
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
                    {users.data.map((user) => (
                      <tr key={user.id} className="transition hover:bg-muted">
                        <td className="max-w-sm px-6 py-4">
                          <p className="text-sm font-medium text-foreground">
                            {user.name}
                          </p>
                          <p className="mt-0.5 text-xs text-muted-foreground">
                            <span className="tabular-nums">#{user.id}</span>
                          </p>
                        </td>
                        <td className="px-6 py-4">
                          <span className="inline-flex items-center gap-1.5 text-sm text-muted-foreground">
                            <Mail className="size-3.5 text-muted-foreground" />
                            {user.email}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          <Select
                            items={ROLE_ITEMS}
                            value={user.role}
                            onValueChange={(value) => updateRole(user, value)}
                          >
                            <SelectTrigger className="h-7 w-28 rounded-full px-2.5 text-xs font-medium [&_svg]:text-current">
                              <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                              {ROLES.map((role) => (
                                <SelectItem key={role} value={role}>
                                  {ROLE_ITEMS[role]}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </td>
                        <td className="px-6 py-4">
                          <span className="inline-flex min-w-8 items-center justify-center rounded-full bg-muted px-2 py-0.5 text-xs font-medium tabular-nums text-foreground">
                            {user.invitations_count}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-sm text-muted-foreground">
                          {formatDate(user.created_at)}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              <Pagination pagination={users} />
            </>
          )}
        </div>
      </AdminLayout>
    </>
  );
}