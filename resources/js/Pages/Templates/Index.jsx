import { Head, Link } from "@inertiajs/react";
import Navbar from "@/Components/Navbar";

export default function TemplatesIndex({ templates }) {
  return (
    <>
      <Head title="Templates" />

      <div className="flex min-h-screen flex-col bg-gray-50">
        <Navbar />

        <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-12 sm:px-6 lg:px-8">
          <div className="mb-8 text-center">
            <h2 className="text-3xl font-bold text-gray-900">
              Choose Your Invitation Template
            </h2>
            <p className="mt-2 text-gray-600">
              Elegant, ready-to-use templates for weddings
            </p>
          </div>

          <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {templates.map((template) => (
              <div
                key={template.id}
                className="group overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200 transition hover:shadow-md"
              >
                <div className="aspect-[3/4] overflow-hidden bg-gray-200">
                  <img
                    src={`/${template.thumbnail_path}`}
                    alt={template.name}
                    className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                  />
                </div>

                <div className="p-4">
                  <h3 className="text-lg font-semibold text-gray-900">
                    {template.name}
                  </h3>
                  <p className="mt-1 text-sm text-gray-600">
                    {template.description}
                  </p>

                  <Link
                    href={route("invitations.create", {
                      template: template.id,
                    })}
                    className="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-primary px-2.5 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/80"
                  >
                    Prepare your invitation with this template
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </main>
      </div>
    </>
  );
}
