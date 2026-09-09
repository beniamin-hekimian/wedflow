import { Head, Link, useForm } from "@inertiajs/react";

import Navbar from "@/Components/Navbar";
import InvitationForm, { EMPTY_EVENT } from "@/Pages/Invitations/InvitationForm";

export default function Edit({ invitation, template, melodies = [], events = [] }) {
  const form = useForm({
    template_id: invitation.template_id,
    melody_id: invitation.melody_id ? String(invitation.melody_id) : "",
    groom_name: invitation.groom_name,
    bride_name: invitation.bride_name,
    event_date: invitation.event_date ? invitation.event_date.slice(0, 10) : "",
    event_time: invitation.event_time ? invitation.event_time.slice(0, 5) : "",
    venue_name: invitation.venue_name,
    venue_address: invitation.venue_address,
    contact_phone: invitation.contact_phone ?? "",
    note: invitation.note ?? "",
    events:
      (invitation.events ?? []).length > 0
        ? invitation.events.map((event) => ({
            event_id: String(event.id),
            time: event.time,
          }))
        : [EMPTY_EVENT, EMPTY_EVENT],
    photos: [],
    existing_photo_ids: (invitation.photos ?? []).map((photo) => photo.id),
  });

  const submit = (event) => {
    event.preventDefault();
    form.put(route("invitations.update", invitation.slug), {
      preserveScroll: true,
    });
  };

  return (
    <>
      <Head title="Edit Invitation" />

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
                    Edit Invitation
                  </h2>
                  <p className="text-sm text-muted-foreground">
                    {invitation.groom_name} & {invitation.bride_name}
                  </p>
                </div>
              </div>

              <Link
                href={route("invitations.index")}
                className="text-sm text-muted-foreground transition hover:text-foreground"
              >
                &larr; Back to invitations
              </Link>
            </div>

            <InvitationForm
              form={form}
              template={template}
              melodies={melodies}
              events={events}
              existingPhotos={invitation.photos ?? []}
              submitLabel="Save Changes"
              onSubmit={submit}
            />
          </div>
        </main>
      </div>
    </>
  );
}