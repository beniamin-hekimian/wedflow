import { Head, Link } from "@inertiajs/react";
import { CalendarDays, Plus } from "lucide-react";

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

const statusVariant = {
  pending: "outline",
  active: "default",
  inactive: "destructive",
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
                          <h3 className="font-semibold text-gray-900">
                            {invitation.groom_name} & {invitation.bride_name}
                          </h3>
                          <p className="text-sm text-gray-500">
                            {invitation.template?.name}
                          </p>
                        </div>
                      </div>

                      <div className="flex items-center gap-2 text-sm text-gray-500">
                        <CalendarDays className="size-4" />
                        {formatDate(invitation.event_date)}
                      </div>

                      <Badge variant={statusVariant[invitation.status]}>
                        {statusLabel[invitation.status]}
                      </Badge>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        </main>
      </div>
    </>
  );
}
