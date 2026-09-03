# Wedding Invitation Feature — Implementation Plan

## Overview
Building a digital wedding invitation platform similar to halaheel.com. A customer creates an invitation, admin activates it after payment, then a public link is shared with guests who can view the invitation, RSVP, and leave wishes.

---

## Phase 1 — Backend (Laravel)

### 1.1 Seeders (populate templates & melodies)
- `TemplateSeeder` — insert 1 template (name, slug, description, thumbnail_path, intro_video_path)
- `MelodySeeder` — insert placeholder melody entries
- `DatabaseSeeder` — call both seeders

### 1.2 Storage setup
- Create `storage/app/public/invitations/{invitation_id}/` directory structure for photo uploads
- Link storage: `php artisan storage:link`

### 1.3 Controllers

**`InvitationController`** (customer, auth middleware)
| Method | Route | Purpose |
|--------|-------|---------|
| `index` | GET `/invitations` | List my invitations |
| `create` | GET `/invitations/create` | Step 1: select template |
| `store` | POST `/invitations` | Save invitation with events |
| `edit` | GET `/invitations/{invitation}/edit` | Edit existing invitation |
| `update` | PUT `/invitations/{invitation}` | Update invitation |
| `uploadPhotos` | POST `/invitations/{invitation}/photos` | Upload photos |
| `deletePhoto` | DELETE `/invitations/{invitation}/photos/{photo}` | Delete a photo |

**`Admin\InvitationController`** (admin only, separate `/admin` prefix)
| Method | Route | Purpose |
|--------|-------|---------|
| `index` | GET `/admin/invitations` | List all pending invitations |
| `show` | GET `/admin/invitations/{invitation}` | Review invitation details |
| `activate` | POST `/admin/invitations/{invitation}/activate` | Set status to `active` |

**`Public\InvitationController`** (no auth)
| Method | Route | Purpose |
|--------|-------|---------|
| `show` | GET `/invitation/{slug}` | Display invitation (only if `active`) |
| `rsvp` | POST `/invitation/{slug}/rsvp` | Store RSVP + wish |

### 1.4 Routes (`routes/web.php`)
```
// Customer (auth)
Route::resource('invitations', InvitationController::class);
Route::post('invitations/{invitation}/photos', ...);
Route::delete('invitations/{invitation}/photos/{photo}', ...);

// Admin (auth + admin middleware)
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('invitations', ...)->name('invitations.index');
    Route::get('invitations/{invitation}', ...)->name('invitations.show');
    Route::post('invitations/{invitation}/activate', ...)->name('invitations.activate');
});

// Public (no auth)
Route::get('invitation/{slug}', ...)->name('invitation.show');
Route::post('invitation/{slug}/rsvp', ...)->name('invitation.rsvp');
```

### 1.5 Admin middleware
- Create `EnsureUserIsAdmin` middleware
- Check `auth()->user()->role === 'admin'`
- Register in `bootstrap/app.php`

### 1.6 Form Requests
- `StoreInvitationRequest` — validate all invitation fields + events array
- `StoreRsvpRequest` — validate guest_name, is_attending, count, message

---

## Phase 2 — Frontend (React + Inertia)

### 2.1 Customer Pages

**`Invitations/Index.jsx`** — My invitations list
- Table/cards showing: template name, groom & bride, status badge, created date
- "Create New" button

**`Invitations/Create.jsx`** — Create invitation (multi-step form)
- Step 1: Select template (show the 1 available template as a card)
- Step 2: Fill form (groom/bride names, parents, date, time, venue, address, welcome message, contact, note)
- Step 3: Select melody (list of available melodies)
- Step 4: Add events (timeline — name, time, position — add/remove rows)
- Step 5: Upload photos (file upload, preview, reorder, delete)
- Submit → redirects to index with status `pending`

**`Invitations/Edit.jsx`** — Edit existing invitation (same form as create, pre-filled)

### 2.2 Admin Pages

**`Admin/Layouts/AdminLayout.jsx`** — Admin layout (separate nav, dark sidebar)

**`Admin/Invitations/Index.jsx`** — Pending invitations list
- Table: invitation slug, groom & bride, customer name, status, date, actions
- Filter by status (pending, active, inactive)

**`Admin/Invitations/Show.jsx`** — Review invitation
- Full preview of invitation data
- "Activate" button (changes status to `active`)

### 2.3 Public Pages

**`Public/Invitation.jsx`** — The actual wedding invitation page
- **Cover**: Full-screen card with intro video/animation. Click to open → music starts playing, card opens, reveals invitation
- **Sections** (scrollable, RTL Arabic):
  1. Groom & Bride names (large, decorative)
  2. Welcome message (Bismillah, etc.)
  3. Parents info
  4. Countdown timer (to event_date)
  5. Event timeline/program
  6. Venue + Google Maps link
  7. Contact info + notes
  8. Photo gallery
  9. RSVP form (name, attending yes/no, companion count, message)
  10. Wishes/guestbook
  11. Footer (save to calendar, share)
- Background music plays from the start (user can toggle)

### 2.4 Layout Updates

**`AuthenticatedLayout.jsx`** — Add "My Invitations" link to nav
**Admin layout** — Separate layout for admin routes

---

## Phase 3 — Admin Detection

- Pass `auth.user` (already shared via Inertia middleware)
- Frontend checks `user.role === 'admin'` to show/hide admin nav link
- Admin middleware protects backend routes

---

## File Summary

| Type | Files to create/edit |
|------|---------------------|
| Migration | None (already done) |
| Model | None (already done) |
| Seeder | `TemplateSeeder.php`, `MelodySeeder.php`, edit `DatabaseSeeder.php` |
| Controller | `InvitationController.php`, `Admin/InvitationController.php`, `Public/InvitationController.php` |
| Form Request | `StoreInvitationRequest.php`, `StoreRsvpRequest.php` |
| Middleware | `EnsureUserIsAdmin.php` |
| Routes | Edit `routes/web.php` |
| Config | Edit `bootstrap/app.php` (register middleware) |
| React Page | `Invitations/Index.jsx`, `Invitations/Create.jsx`, `Invitations/Edit.jsx` |
| React Page | `Admin/Layouts/AdminLayout.jsx`, `Admin/Invitations/Index.jsx`, `Admin/Invitations/Show.jsx` |
| React Page | `Public/Invitation.jsx` |
| Layout | Edit `AuthenticatedLayout.jsx` (add nav link) |
| Assets | Placeholder audio files, intro video |
