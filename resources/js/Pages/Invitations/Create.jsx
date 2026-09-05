import { Head, Link, useForm } from "@inertiajs/react";
import { useRef } from "react";
import { Loader2, ImagePlus, Plus, Trash2 } from "lucide-react";

import Navbar from "@/Components/Navbar";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

const MAX_EVENTS = 4;
const MIN_EVENTS = 2;
const MAX_PHOTOS = 5;

const emptyEvent = { name: "", time: "" };

function Field({ label, error, children }) {
  return (
    <div className="space-y-1.5">
      <Label>{label}</Label>
      {children}
      {error && <p className="text-sm text-destructive">{error}</p>}
    </div>
  );
}

export default function Create({ template, melodies = [] }) {
  const photoInputRef = useRef(null);
  const photoObjectUrls = useRef(new Map());

  const getPhotoPreview = (file) => {
    if (!photoObjectUrls.current.has(file)) {
      photoObjectUrls.current.set(file, URL.createObjectURL(file));
    }
    return photoObjectUrls.current.get(file);
  };

  const form = useForm({
    template_id: template.id,
    melody_id: "",
    groom_name: "",
    bride_name: "",
    groom_parents: "",
    bride_parents: "",
    event_date: "",
    event_time: "",
    venue_name: "",
    venue_address: "",
    welcome_message: "",
    contact_name: "",
    contact_phone: "",
    note: "",
    events: [emptyEvent, emptyEvent],
    photos: [],
  });

  const addEvent = () => {
    if (form.data.events.length >= MAX_EVENTS) return;
    form.setData("events", [...form.data.events, { ...emptyEvent }]);
  };

  const updateEvent = (index, field, value) => {
    form.setData(
      "events",
      form.data.events.map((event, i) =>
        i === index ? { ...event, [field]: value } : event,
      ),
    );
  };

  const removeEvent = (index) => {
    if (form.data.events.length <= MIN_EVENTS) return;
    form.setData("events", form.data.events.filter((_, i) => i !== index));
  };

  const addPhotos = (event) => {
    const files = Array.from(event.target.files ?? []);
    const remaining = MAX_PHOTOS - form.data.photos.length;
    const added = files.slice(0, Math.max(remaining, 0));
    form.setData("photos", [...form.data.photos, ...added]);
    event.target.value = "";
  };

  const removePhoto = (index) => {
    const file = form.data.photos[index];
    const preview = photoObjectUrls.current.get(file);
    if (preview) {
      URL.revokeObjectURL(preview);
      photoObjectUrls.current.delete(file);
    }
    form.setData("photos", form.data.photos.filter((_, i) => i !== index));
  };

  const submit = (event) => {
    event.preventDefault();
    form.post(route("invitations.store"), { preserveScroll: true });
  };

  const selectedMelody = melodies.find(
    (melody) => String(melody.id) === String(form.data.melody_id),
  );

  return (
    <>
      <Head title="Create Invitation" />

      <div className="flex min-h-screen flex-col bg-gray-50">
        <Navbar />

        <main className="flex-1">
          <div className="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <h2 className="mb-8 text-xl font-semibold leading-tight text-gray-800">
              Create Invitation
            </h2>

            <div className="space-y-6">
              <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <img
                src={`/${template.thumbnail_path}`}
                alt={template.name}
                className="h-14 w-12 rounded-md object-cover ring-1 ring-gray-200"
              />
              <div>
                <p className="text-sm font-medium text-gray-900">{template.name}</p>
                <p className="text-sm text-gray-600">{template.description}</p>
              </div>
            </div>

            <Link
              href={route("templates.index")}
              className="text-sm text-gray-500 transition hover:text-gray-700"
            >
              &larr; Back to templates
            </Link>
          </div>

          <form onSubmit={submit} className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Couple</CardTitle>
                <CardDescription>
                  Names as they will appear on the invitation.
                </CardDescription>
              </CardHeader>
              <CardContent className="grid gap-4 sm:grid-cols-2">
                <Field label="Groom's name" error={form.errors.groom_name}>
                  <Input
                    value={form.data.groom_name}
                    onChange={(e) => form.setData("groom_name", e.target.value)}
                    placeholder="John Smith"
                  />
                </Field>

                <Field label="Bride's name" error={form.errors.bride_name}>
                  <Input
                    value={form.data.bride_name}
                    onChange={(e) => form.setData("bride_name", e.target.value)}
                    placeholder="Jane Doe"
                  />
                </Field>

                <Field label="Groom's parents" error={form.errors.groom_parents}>
                  <Input
                    value={form.data.groom_parents}
                    onChange={(e) => form.setData("groom_parents", e.target.value)}
                    placeholder="Mr. & Mrs. Smith"
                  />
                </Field>

                <Field label="Bride's parents" error={form.errors.bride_parents}>
                  <Input
                    value={form.data.bride_parents}
                    onChange={(e) => form.setData("bride_parents", e.target.value)}
                    placeholder="Mr. & Mrs. Doe"
                  />
                </Field>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Event</CardTitle>
                <CardDescription>
                  When and where the celebration takes place.
                </CardDescription>
              </CardHeader>
              <CardContent className="grid gap-4 sm:grid-cols-2">
                <Field label="Event date" error={form.errors.event_date}>
                  <Input
                    type="date"
                    value={form.data.event_date}
                    onChange={(e) => form.setData("event_date", e.target.value)}
                  />
                </Field>

                <Field label="Event time" error={form.errors.event_time}>
                  <Input
                    type="time"
                    value={form.data.event_time}
                    onChange={(e) => form.setData("event_time", e.target.value)}
                  />
                </Field>

                <Field label="Venue name" error={form.errors.venue_name}>
                  <Input
                    value={form.data.venue_name}
                    onChange={(e) => form.setData("venue_name", e.target.value)}
                    placeholder="Grand Palace"
                  />
                </Field>

                <Field label="Venue address" error={form.errors.venue_address}>
                  <Input
                    value={form.data.venue_address}
                    onChange={(e) => form.setData("venue_address", e.target.value)}
                    placeholder="1 Wedding Avenue, City"
                  />
                </Field>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Music</CardTitle>
                <CardDescription>
                  A melody to play on the public invitation page.
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  <Field label="Melody" error={form.errors.melody_id}>
                    <Select
                      items={Object.fromEntries(
                        melodies.map((melody) => [String(melody.id), melody.name]),
                      )}
                      value={form.data.melody_id || null}
                      onValueChange={(value) =>
                        form.setData("melody_id", value ?? "")
                      }
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Choose a melody" />
                      </SelectTrigger>
                      <SelectContent>
                        {melodies.map((melody) => (
                          <SelectItem key={melody.id} value={String(melody.id)}>
                            {melody.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </Field>

                  {selectedMelody ? (
                    <div className="space-y-1">
                      <p className="text-xs text-muted-foreground">
                        Preview: {selectedMelody.name}
                      </p>
                      <audio
                        key={selectedMelody.id}
                        controls
                        src={`/${selectedMelody.file_path}`}
                        className="h-9 w-full"
                      />
                    </div>
                  ) : (
                    <p className="text-xs text-muted-foreground">
                      Select a melody to listen to it before saving.
                    </p>
                  )}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Message</CardTitle>
                <CardDescription>
                  Optional notes shown on the invitation.
                </CardDescription>
              </CardHeader>
              <CardContent className="grid gap-4 sm:grid-cols-2">
                <Field label="Welcome message" error={form.errors.welcome_message}>
                  <Textarea
                    value={form.data.welcome_message}
                    onChange={(e) =>
                      form.setData("welcome_message", e.target.value)
                    }
                    placeholder="A short welcome for your guests..."
                  />
                </Field>

                <div className="grid gap-4">
                  <Field label="Contact name" error={form.errors.contact_name}>
                    <Input
                      value={form.data.contact_name}
                      onChange={(e) => form.setData("contact_name", e.target.value)}
                      placeholder="Who to contact"
                    />
                  </Field>

                  <Field label="Contact phone" error={form.errors.contact_phone}>
                    <Input
                      value={form.data.contact_phone}
                      onChange={(e) => form.setData("contact_phone", e.target.value)}
                      placeholder="+1 555 000 0000"
                    />
                  </Field>
                </div>

                <div className="sm:col-span-2">
                  <Field label="Note" error={form.errors.note}>
                    <Textarea
                      value={form.data.note}
                      onChange={(e) => form.setData("note", e.target.value)}
                      placeholder="Dress code, directions, anything else..."
                    />
                  </Field>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Timeline</CardTitle>
                <CardDescription>
                  {MIN_EVENTS} to {MAX_EVENTS} schedule points shown on the public
                  invitation.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-3">
                {form.data.events.map((event, index) => (
                  <div key={index} className="space-y-1.5">
                    <div className="flex items-start gap-2">
                      <div className="flex-1 space-y-1.5">
                        <Input
                          value={event.name}
                          onChange={(e) =>
                            updateEvent(index, "name", e.target.value)
                          }
                          placeholder="Wedding Ceremony"
                        />
                        {form.errors[`events.${index}.name`] && (
                          <p className="text-sm text-destructive">
                            {form.errors[`events.${index}.name`]}
                          </p>
                        )}
                      </div>

                      <div className="w-32 shrink-0 space-y-1.5">
                        <Input
                          type="time"
                          value={event.time}
                          onChange={(e) =>
                            updateEvent(index, "time", e.target.value)
                          }
                        />
                        {form.errors[`events.${index}.time`] && (
                          <p className="text-sm text-destructive">
                            {form.errors[`events.${index}.time`]}
                          </p>
                        )}
                      </div>

                      <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        disabled={form.data.events.length <= MIN_EVENTS}
                        onClick={() => removeEvent(index)}
                        aria-label="Remove timeline point"
                      >
                        <Trash2 />
                      </Button>
                    </div>
                  </div>
                ))}

                {form.errors.events && (
                  <p className="text-sm text-destructive">{form.errors.events}</p>
                )}

                <Button
                  type="button"
                  variant="outline"
                  disabled={form.data.events.length >= MAX_EVENTS}
                  onClick={addEvent}
                >
                  <Plus /> Add timeline point
                </Button>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Photo Gallery</CardTitle>
                <CardDescription>
                  Up to {MAX_PHOTOS} images for the gallery on the public
                  invitation.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <input
                  ref={photoInputRef}
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  multiple
                  className="hidden"
                  onChange={addPhotos}
                />

                <div className="grid grid-cols-3 gap-3 sm:grid-cols-5">
                  {form.data.photos.map((photo, index) => (
                    <div
                      key={`${photo.name}-${index}`}
                      className="group relative aspect-square overflow-hidden rounded-md ring-1 ring-gray-200"
                    >
                      <img
                        src={getPhotoPreview(photo)}
                        alt={photo.name}
                        className="h-full w-full object-cover"
                      />
                      <button
                        type="button"
                        onClick={() => removePhoto(index)}
                        className="absolute right-1 top-1 rounded-full bg-black/60 p-1 text-white opacity-0 transition group-hover:opacity-100"
                        aria-label="Remove photo"
                      >
                        <Trash2 className="size-3.5" />
                      </button>
                    </div>
                  ))}

                  {form.data.photos.length < MAX_PHOTOS && (
                    <button
                      type="button"
                      onClick={() => photoInputRef.current?.click()}
                      className="flex aspect-square flex-col items-center justify-center gap-1 rounded-md border border-dashed border-input text-muted-foreground transition hover:border-primary hover:text-primary"
                    >
                      <ImagePlus className="size-5" />
                      <span className="text-xs">
                        {form.data.photos.length}/{MAX_PHOTOS}
                      </span>
                    </button>
                  )}
                </div>

                {form.errors.photos && (
                  <p className="text-sm text-destructive">{form.errors.photos}</p>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardContent className="space-y-3 pt-6">
                {form.progress && (
                  <div className="h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
                    <div
                      className="h-full bg-primary transition-all"
                      style={{ width: `${form.progress.percentage}%` }}
                    />
                  </div>
                )}

                {form.recentlySuccessful && (
                  <p className="text-sm text-emerald-600">
                    Invitation created successfully.
                  </p>
                )}

                <Button
                  type="submit"
                  size="lg"
                  className="w-full"
                  disabled={form.processing}
                >
                  {form.processing ? (
                    <>
                      <Loader2 className="animate-spin" /> Saving…
                    </>
                  ) : (
                    "Create Invitation"
                  )}
                </Button>
              </CardContent>
            </Card>
          </form>
          </div>
        </div>
      </main>
    </div>
    </>
  );
}