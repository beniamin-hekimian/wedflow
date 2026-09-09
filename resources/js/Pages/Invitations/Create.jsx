import { Head, Link, useForm } from "@inertiajs/react";

import Navbar from "@/Components/Navbar";
import InvitationForm, { EMPTY_EVENT } from "@/Pages/Invitations/InvitationForm";

export default function Create({ template, melodies = [] }) {
  const form = useForm({
    template_id: template.id,
    melody_id: "",
    groom_name: "",
    bride_name: "",
    event_date: "",
    event_time: "",
    venue_name: "",
    venue_address: "",
    contact_phone: "",
    note: "",
    events: [EMPTY_EVENT, EMPTY_EVENT],
    photos: [],
    existing_photo_ids: [],
  });

  const submit = (event) => {
    event.preventDefault();
    form.post(route("invitations.store"), { preserveScroll: true });
  };

  return (
    <>
      <Head title="Create Invitation" />

      <div className="flex min-h-screen flex-col bg-background">
        <Navbar />

        <main className="flex-1">
          <div className="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <div className="mb-8 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <img
                  src={`/${template.thumbnail_path}`}
                  alt={template.name}
                  className="h-14 w-12 rounded-md object-cover ring-1 ring-border"
                />
                <div>
                  <h2 className="font-display text-2xl font-semibold leading-tight text-foreground">
                    Create Invitation
                  </h2>
                  <p className="text-sm text-muted-foreground">{template.name}</p>
                </div>
              </div>

              <Link
                href={route("templates.index")}
                className="text-sm text-muted-foreground transition hover:text-foreground"
              >
                &larr; Back to templates
              </Link>
            </div>

            <InvitationForm
              form={form}
              template={template}
              melodies={melodies}
              submitLabel="Create Invitation"
              onSubmit={submit}
            />
          </div>
        </main>
      </div>
    </>
  );
}