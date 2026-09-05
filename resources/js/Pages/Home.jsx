import { Head, Link, usePage } from '@inertiajs/react';
import { FileText, Plus, Sparkles } from 'lucide-react';

import Navbar from '@/Components/Navbar';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';

export default function Home() {
  const user = usePage().props.auth.user;

  return (
    <>
      <Head title="Home" />

      <div className="flex min-h-screen flex-col bg-gray-50">
        <Navbar />

        <main className="flex-1">
          <div className="mx-auto max-w-7xl space-y-6 px-4 py-12 sm:px-6 lg:px-8">
            <div className="rounded-xl bg-white p-8 shadow-sm">
              {user ? (
                <>
                  <h1 className="text-3xl font-bold tracking-tight text-gray-900">
                    Welcome back, {user.name}
                  </h1>
                  <p className="mt-2 text-lg text-gray-600">
                    Design your digital wedding invitation from a beautiful
                    template and share it with your guests.
                  </p>
                </>
              ) : (
                <>
                  <h1 className="text-3xl font-bold tracking-tight text-gray-900">
                    Digital wedding invitations
                  </h1>
                  <p className="mt-2 text-lg text-gray-600">
                    Create a stunning wedding invitation and share it with your
                    guests, in minutes.
                  </p>
                </>
              )}

              <div className="mt-6 flex flex-wrap gap-3">
                <Link href={route('templates.index')}>
                  <Button size="lg">
                    <Sparkles /> Browse Templates
                  </Button>
                </Link>
                {user ? (
                  <Link href={route('invitations.index')}>
                    <Button variant="outline" size="lg">
                      <FileText /> My Invitations
                    </Button>
                  </Link>
                ) : (
                  <Link href={route('register')}>
                    <Button variant="outline" size="lg">
                      Get started
                    </Button>
                  </Link>
                )}
              </div>
            </div>

            <div className="grid gap-6 sm:grid-cols-3">
              <Card>
                <CardHeader>
                  <CardTitle>Pick a template</CardTitle>
                  <CardDescription>
                    Choose from our elegant, ready-to-use designs.
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <Link href={route('templates.index')}>
                    <Button variant="outline" className="w-full">
                      <Plus /> Browse templates
                    </Button>
                  </Link>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Fill in the details</CardTitle>
                  <CardDescription>
                    Couple names, venue, timeline and gallery photos.
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="text-sm text-muted-foreground">
                    Tell your story and schedule the celebration.
                  </p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Share it</CardTitle>
                  <CardDescription>
                    Once approved, your guests get a beautiful public
                    invitation.
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="text-sm text-muted-foreground">
                    A private web page with RSVP and wishes.
                  </p>
                </CardContent>
              </Card>
            </div>
          </div>
        </main>
      </div>
    </>
  );
}