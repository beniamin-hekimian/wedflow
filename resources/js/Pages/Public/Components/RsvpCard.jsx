import { useForm } from '@inertiajs/react';
import { Minus, Plus } from 'lucide-react';
import { cn } from '@/lib/utils';

const MAX_GUESTS = 10;
const MAX_WISH_LENGTH = 1000;

const initials = (name) => {
  const parts = String(name ?? '').trim().split(/\s+/).filter(Boolean);
  const first = parts[0]?.[0] ?? '';
  const last = parts.length > 1 ? parts[parts.length - 1][0] : '';

  return (first + last).toUpperCase();
};

export default function RsvpCard({ invitation, wishes = [] }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    guest_name: '',
    is_attending: true,
    count: 1,
    message: '',
  });

  const submit = (e) => {
    e.preventDefault();

    post(route('rsvp.store', { slug: invitation.slug }), {
      preserveScroll: true,
      onSuccess: () =>
        reset('guest_name', 'count', 'message'),
    });
  };

  const chooseAttendance = (attending) => {
    setData({
      ...data,
      is_attending: attending,
      count: attending ? Math.max(1, data.count) : 0,
    });
  };

  return (
    <>
      <section className="card rsvp-card">
        <p className="card__eyebrow">Kindly reply</p>
        <h2 className="serif-display rsvp-card__title">RSVP</h2>
        <p className="card__text">
          Please respond by sharing whether you can join us for our celebration.
        </p>

        <form className="rsvp-form" onSubmit={submit}>
          <div className="rsvp-field">
            <label className="rsvp-field__label" htmlFor="rsvp-guest-name">
              Your name
            </label>
            <input
              id="rsvp-guest-name"
              type="text"
              className="rsvp-input"
              placeholder="e.g. Amelia Carter"
              value={data.guest_name}
              onChange={(e) => setData('guest_name', e.target.value)}
            />
            {errors.guest_name && (
              <p className="rsvp-error">{errors.guest_name}</p>
            )}
          </div>

          <div className="rsvp-field">
            <span className="rsvp-field__label">Will you attend?</span>
            <div className="rsvp-choice" role="group" aria-label="Will you attend?">
              <button
                type="button"
                className={cn('rsvp-choice__option', {
                  'rsvp-choice__option--active': data.is_attending,
                })}
                onClick={() => chooseAttendance(true)}
              >
                Joyfully accepts
              </button>
              <button
                type="button"
                className={cn('rsvp-choice__option', {
                  'rsvp-choice__option--active': !data.is_attending,
                })}
                onClick={() => chooseAttendance(false)}
              >
                Regretfully declines
              </button>
            </div>
            {errors.is_attending && (
              <p className="rsvp-error">{errors.is_attending}</p>
            )}
          </div>

          <div
            className={cn('rsvp-field', {
              'rsvp-field--disabled': !data.is_attending,
            })}
          >
            <label className="rsvp-field__label" id="rsvp-count-label">
              Number of guests
            </label>
            <div className="rsvp-stepper">
              <button
                type="button"
                className="rsvp-stepper__btn"
                disabled={!data.is_attending || data.count <= 1}
                onClick={() => setData('count', Math.max(1, data.count - 1))}
                aria-label="Decrease guests"
              >
                <Minus className="size-4" />
              </button>
              <input
                id="rsvp-count"
                type="number"
                min={1}
                max={MAX_GUESTS}
                className="rsvp-input rsvp-stepper__value"
                value={data.is_attending ? data.count : 0}
                disabled={!data.is_attending}
                aria-labelledby="rsvp-count-label"
                onChange={(e) =>
                  setData(
                    'count',
                    Math.max(1, Math.min(MAX_GUESTS, Number(e.target.value) || 1)),
                  )
                }
              />
              <button
                type="button"
                className="rsvp-stepper__btn"
                disabled={!data.is_attending || data.count >= MAX_GUESTS}
                onClick={() => setData('count', Math.min(MAX_GUESTS, data.count + 1))}
                aria-label="Increase guests"
              >
                <Plus className="size-4" />
              </button>
            </div>
            {errors.count && <p className="rsvp-error">{errors.count}</p>}
          </div>

          <div className="rsvp-field">
            <label className="rsvp-field__label" htmlFor="rsvp-message">
              A wish for the couple <span className="rsvp-field__optional">(optional)</span>
            </label>
            <textarea
              id="rsvp-message"
              className="rsvp-textarea"
              rows={3}
              maxLength={MAX_WISH_LENGTH}
              placeholder="Write a heartfelt message..."
              value={data.message}
              onChange={(e) => setData('message', e.target.value)}
            />
            {errors.message && <p className="rsvp-error">{errors.message}</p>}
          </div>

          <button
            type="submit"
            className="rsvp-submit"
            disabled={processing}
          >
            {processing ? 'Sending...' : 'Send RSVP'}
          </button>
        </form>
      </section>

      {wishes.length > 0 && (
        <section className="card wishes-section">
          <p className="card__eyebrow">Guest book</p>
          <h2 className="serif-display card__title">Wishes for us</h2>

          <ul className="wishes">
            {wishes.map((wish) => (
              <li key={wish.id} className="wishes__item">
                <span className="wishes__avatar">{initials(wish.guest_name)}</span>
                <div className="wishes__bubble">
                  <p className="wishes__name">{wish.guest_name}</p>
                  <p className="wishes__message">{wish.message}</p>
                </div>
              </li>
            ))}
          </ul>
        </section>
      )}
    </>
  );
}