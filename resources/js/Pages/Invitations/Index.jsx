import { useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import { CalendarDays, Check, Copy, MapPin, Pencil, Plus, Trash2, Users } from "lucide-react";

import Modal from "@/Components/Modal";
import Navbar from "@/Components/Navbar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { cn } from "@/lib/utils";

const statusDotClass = {
  pending: "bg-yellow-500",
  active: "bg-emerald-500",
  inactive: "bg-red-500",
};

const statusLabel = {
  pending: "Pending",
  active: "Active",
  inactive: "Inactive",
};

const formatDate = (value) => {
  if (!value) return "—";
  return new Date(value).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

export default function Index({ invitations = [] }) {
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [copiedId, setCopiedId] = useState(null);

  const copyLink = (invitation) => {
    if (!navigator.clipboard) return;
    navigator.clipboard.writeText(`${window.location.origin}/${invitation.slug}`);
    setCopiedId(invitation.id);
    setTimeout(() => setCopiedId(null), 2000);
  };

  const confirmDelete = () => {
    if (!deleteTarget) return;
    router.delete(route("invitations.destroy", deleteTarget.slug), {
      preserveScroll: true,
    });
    setDeleteTarget(null);
  };

  return (
    <>
      <Head title="My Invitations" />

      <div className="flex min-h-screen flex-col bg-gray-50">
        <Navbar />

        <main className="flex-1">
          <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div className="mb-8 flex items-center justify-between">
              <h2 className="text-xl font-semibold leading-tight text-gray-800">
                My Invitations
              </h2>
              <Link href={route("templates.index")}>
                <Button>
                  <Plus /> New Invitation
                </Button>
              </Link>
            </div>

            {invitations.length === 0 ? (
              <Card>
                <CardHeader>
                  <CardTitle>No invitations yet</CardTitle>
                  <CardDescription>
                    Browse our templates and create your first invitation.
                  </CardDescription>
                </CardHeader>
              </Card>
            ) : (
              <div className="space-y-4">
                {invitations.map((invitation) => (
                  <Card
                    key={invitation.id}
                    className="flex flex-col sm:flex-row sm:items-center"
                  >
                    <CardContent className="flex flex-1 flex-col gap-4 p-6 sm:flex-row sm:items-center">
                      <div className="flex flex-1 items-center gap-4">
                        <img
                          src={`/${invitation.template?.thumbnail_path}`}
                          alt={invitation.template?.name}
                          className="h-16 w-12 rounded-md object-cover ring-1 ring-gray-200"
                        />
                        <div>
                          <div className="flex items-center gap-1.5">
                            <Link
                              href={`/${invitation.slug}`}
                              className="font-bold text-gray-900 transition hover:text-blue-600"
                            >
                              {invitation.groom_name} & {invitation.bride_name}
                            </Link>
                            <button
                              type="button"
                              onClick={() => copyLink(invitation)}
                              className="text-gray-400 transition hover:text-gray-600"
                              aria-label="Copy invitation link"
                            >
                              {copiedId === invitation.id ? (
                                <Check className="size-4 text-emerald-500" />
                              ) : (
                                <Copy className="size-4" />
                              )}
                            </button>
                          </div>
                          <div className="mt-1 flex items-center gap-2 text-sm text-gray-500">
                            <span className="flex items-center gap-1">
                              <CalendarDays className="size-4" />
                              {formatDate(invitation.event_date)}
                            </span>
                            <span>·</span>
                            <span className="flex min-w-0 items-center gap-1">
                              <MapPin className="size-4 shrink-0" />
                              <span className="truncate">
                                {invitation.venue_name || "—"}
                              </span>
                            </span>
                          </div>
                        </div>
                      </div>

                      <div className="flex shrink-0 items-center gap-3">
                        <div className="flex flex-col items-end gap-1">
                          <Badge
                            variant="outline"
                            className="rounded-full"
                          >
                            <span
                              className={cn(
                                "size-2 shrink-0 rounded-full",
                                statusDotClass[invitation.status],
                              )}
                            />
                            {statusLabel[invitation.status]}
                          </Badge>
                        </div>

                        <div className="flex items-center gap-2">
                          <Link href={route("invitations.responses", invitation.slug)}>
                            <Button variant="secondary" size="sm">
                              <Users /> Responses
                            </Button>
                          </Link>
                          <Link href={route("invitations.edit", invitation.slug)}>
                            <Button variant="outline" size="sm">
                              <Pencil /> Edit
                            </Button>
                          </Link>
                          <Button
                            variant="ghost"
                            size="sm"
                            className="text-destructive hover:text-destructive"
                            onClick={() => setDeleteTarget(invitation)}
                          >
                            <Trash2 /> Delete
                          </Button>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        </main>
      </div>

      <Modal
        show={deleteTarget !== null}
        onClose={() => setDeleteTarget(null)}
        maxWidth="sm"
      >
        <div className="p-6">
          <h3 className="text-lg font-medium text-gray-900">Delete invitation?</h3>
          <p className="mt-2 text-sm text-gray-600">
            Are you sure you want to delete the invitation for{" "}
            {deleteTarget?.groom_name} & {deleteTarget?.bride_name}? This action
            cannot be undone.
          </p>

          <div className="mt-6 flex justify-end gap-3">
            <Button variant="outline" onClick={() => setDeleteTarget(null)}>
              Cancel
            </Button>
            <Button variant="destructive" onClick={confirmDelete}>
              Delete
            </Button>
          </div>
        </div>
      </Modal>
    </>
  );
}