import { Head, Link } from "@inertiajs/react";
import Navbar from "@/Components/Navbar";
import { Button } from "@/Components/ui/button";

export default function Welcome() {
  return (
    <>
      <Head title="Welcome" />

      <div className="flex min-h-screen flex-col bg-gray-50">
        <Navbar />

        <main className="flex flex-1 items-center justify-center px-4">
          <div className="text-center">
            <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
              Welcome
            </h1>
            <p className="mt-4 text-lg text-gray-600">
              Create beautiful digital wedding invitations in minutes.
            </p>
            <div className="mt-8 flex justify-center gap-3">
              <Link href={route("templates.index")}>
                <Button>Browse Templates</Button>
              </Link>
              <Link href="/">
                <Button variant="outline">Learn More</Button>
              </Link>
            </div>
          </div>
        </main>
      </div>
    </>
  );
}
