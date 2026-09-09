import { useRef, useState } from "react";
import { Loader2, ImagePlus, Plus, Trash2 } from "lucide-react";

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

export const EMPTY_EVENT = { event_id: "", time: "" };

function Field({ label, error, required, children }) {
  return (
    <div className="space-y-1.5">
      <Label>
        {label}
        {required && <span className="text-destructive"> *</span>}
      </Label>
      {children}
      {error && <p className="text-sm text-destructive">{error}</p>}
    </div>
  );
}

export default function InvitationForm({
  form,
  template,
  melodies = [],
  events = [],
  existingPhotos = [],
  submitLabel,
  onSubmit,
}) {
  const [existing, setExisting] = useState(existingPhotos);
  const photoInputRef = useRef(null);
  const photoObjectUrls = useRef(new Map());

  const getPhotoPreview = (file) => {
    if (!photoObjectUrls.current.has(file)) {
      photoObjectUrls.current.set(file, URL.createObjectURL(file));
    }
    return photoObjectUrls.current.get(file);
  };

  const addEvent = () => {
    if (form.data.events.length >= MAX_EVENTS) return;
    form.setData("events", [...form.data.events, { ...EMPTY_EVENT }]);
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
    const remaining = MAX_PHOTOS - (existing.length + form.data.photos.length);
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

  const removeExistingPhoto = (id) => {
    const next = existing.filter((photo) => photo.id !== id);
    setExisting(next);
    form.setData(
      "existing_photo_ids",
      next.map((photo) => photo.id),
    );
  };

  const selectedMelody = melodies.find(
    (melody) => String(melody.id) === String(form.data.melody_id),
  );

  return (
    <form onSubmit={onSubmit} className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Couple</CardTitle>
          <CardDescription>
            Names as they will appear on the invitation.
          </CardDescription>
        </CardHeader>
        <CardContent className="grid gap-4 sm:grid-cols-2">
          <Field label="Groom's name" error={form.errors.groom_name} required>
            <Input
              value={form.data.groom_name}
              onChange={(e) => form.setData("groom_name", e.target.value)}
              placeholder="John Smith"
            />
          </Field>

          <Field label="Bride's name" error={form.errors.bride_name} required>
            <Input
              value={form.data.bride_name}
              onChange={(e) => form.setData("bride_name", e.target.value)}
              placeholder="Jane Doe"
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
            <Field label="Melody" error={form.errors.melody_id} required>
              <Select
                items={Object.fromEntries(
                  melodies.map((melody) => [String(melody.id), melody.name]),
                )}
                value={form.data.melody_id || null}
                onValueChange={(value) => form.setData("melody_id", value ?? "")}
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
          <CardTitle>The big day</CardTitle>
          <CardDescription>When the celebration takes place.</CardDescription>
        </CardHeader>
        <CardContent className="grid gap-4 sm:grid-cols-2">
          <Field label="Event date" error={form.errors.event_date} required>
            <Input
              type="date"
              value={form.data.event_date}
              onChange={(e) => form.setData("event_date", e.target.value)}
            />
          </Field>

          <Field label="Event time" error={form.errors.event_time} required>
            <Input
              type="time"
              value={form.data.event_time}
              onChange={(e) => form.setData("event_time", e.target.value)}
            />
          </Field>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>
            Timeline <span className="text-destructive">*</span>
          </CardTitle>
          <CardDescription>
            Pick {MIN_EVENTS} to {MAX_EVENTS} moments of your day. The order
            shown below is the order displayed on the invitation, and you set a
            clock time for each.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          {form.data.events.map((event, index) => {
            const selectedIds = form.data.events
              .map((item) => item.event_id)
              .filter(Boolean);
            const available = events.filter(
              (catalogEvent) =>
                String(catalogEvent.id) === String(event.event_id) ||
                !selectedIds.includes(String(catalogEvent.id)),
            );

            return (
              <div key={index} className="space-y-1.5">
                <div className="flex items-start gap-2">
                  <div className="flex-1 space-y-1.5">
                    <Select
                      items={Object.fromEntries(
                        available.map((catalogEvent) => [
                          String(catalogEvent.id),
                          catalogEvent.name,
                        ]),
                      )}
                      value={event.event_id || null}
                      onValueChange={(value) =>
                        updateEvent(index, "event_id", value ?? "")
                      }
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Choose a moment" />
                      </SelectTrigger>
                      <SelectContent>
                        {available.map((catalogEvent) => (
                          <SelectItem
                            key={catalogEvent.id}
                            value={String(catalogEvent.id)}
                          >
                            {catalogEvent.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {form.errors[`events.${index}.event_id`] && (
                      <p className="text-sm text-destructive">
                        {form.errors[`events.${index}.event_id`]}
                      </p>
                    )}
                  </div>

                  <div className="w-32 shrink-0 space-y-1.5">
                    <Input
                      type="time"
                      value={event.time}
                      onChange={(e) => updateEvent(index, "time", e.target.value)}
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
                    aria-label="Remove timeline moment"
                  >
                    <Trash2 />
                  </Button>
                </div>
              </div>
            );
          })}

          {form.errors.events && (
            <p className="text-sm text-destructive">{form.errors.events}</p>
          )}

          <Button
            type="button"
            variant="outline"
            disabled={form.data.events.length >= MAX_EVENTS}
            onClick={addEvent}
          >
            <Plus /> Add a moment
          </Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Location</CardTitle>
          <CardDescription>Where the celebration takes place.</CardDescription>
        </CardHeader>
        <CardContent className="grid gap-4 sm:grid-cols-2">
          <Field label="Venue name" error={form.errors.venue_name} required>
            <Input
              value={form.data.venue_name}
              onChange={(e) => form.setData("venue_name", e.target.value)}
              placeholder="Grand Palace"
            />
          </Field>

          <Field label="Venue address" error={form.errors.venue_address} required>
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
          <CardTitle>Photo Gallery</CardTitle>
          <CardDescription>
            Up to {MAX_PHOTOS} images for the gallery on the public invitation.
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
            {existing.map((photo) => (
              <div
                key={photo.id}
                className="group relative aspect-square overflow-hidden rounded-md ring-1 ring-border"
              >
                <img
                  src={`/storage/${photo.photo_path}`}
                  alt={`Existing photo ${photo.id}`}
                  className="h-full w-full object-cover"
                />
                <button
                  type="button"
                  onClick={() => removeExistingPhoto(photo.id)}
                  className="absolute right-1 top-1 rounded-full bg-black/60 p-1 text-white opacity-0 transition group-hover:opacity-100"
                  aria-label="Remove photo"
                >
                  <Trash2 className="size-3.5" />
                </button>
              </div>
            ))}

            {form.data.photos.map((photo, index) => (
              <div
                key={`${photo.name}-${index}`}
                className="group relative aspect-square overflow-hidden rounded-md ring-1 ring-border"
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

            {existing.length + form.data.photos.length < MAX_PHOTOS && (
              <button
                type="button"
                onClick={() => photoInputRef.current?.click()}
                className="flex aspect-square flex-col items-center justify-center gap-1 rounded-md border border-dashed border-input text-muted-foreground transition hover:border-primary hover:text-primary"
              >
                <ImagePlus className="size-5" />
                <span className="text-xs">
                  {existing.length + form.data.photos.length}/{MAX_PHOTOS}
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
        <CardHeader>
          <CardTitle>A note</CardTitle>
          <CardDescription>
            A message for your guests, shown on the invitation.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Field label="Note" error={form.errors.note}>
            <Input
              value={form.data.note}
              onChange={(e) => form.setData("note", e.target.value)}
              placeholder="Dress code, directions, anything else..."
            />
          </Field>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Contact</CardTitle>
          <CardDescription>How guests can reach you with questions.</CardDescription>
        </CardHeader>
        <CardContent>
          <Field label="Contact phone" error={form.errors.contact_phone}>
            <Input
              value={form.data.contact_phone}
              onChange={(e) => form.setData("contact_phone", e.target.value)}
              placeholder="0912345678"
            />
          </Field>
        </CardContent>
      </Card>

      <Card>
        <CardContent className="space-y-3 pt-6">
          {form.progress && (
            <div className="h-1.5 w-full overflow-hidden rounded-full bg-muted">
              <div
                className="h-full bg-primary transition-all"
                style={{ width: `${form.progress.percentage}%` }}
              />
            </div>
          )}

          {form.recentlySuccessful && (
            <p className="text-sm text-emerald-600">
              {submitLabel} successful.
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
              submitLabel
            )}
          </Button>
        </CardContent>
      </Card>
    </form>
  );
}