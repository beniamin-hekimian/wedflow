import { Head, Link } from "@inertiajs/react";
import { useEffect, useMemo, useRef, useState } from "react";
import { MapPin, Music, Pause } from "lucide-react";

import RsvpCard from "../Components/RsvpCard";
import "../../../../css/invitations/royal.css";

const INTRO_FADE_MS = 1200;

const SEAL_NOISE = [
  0.8, 1.9, -1.3, 2.6, -2.1, 1.1, -2.9, 0.5, 2.3, -1.7, 0.7, 3, -0.5, -2.3, 1.6,
  -2, 0.4, 2.7, -1.5, -1.8, 2.4, 0.8, -2.6, 1.2, -0.9, 1.7,
];

function buildSealPath() {
  const segments = SEAL_NOISE.length;
  let d = "";

  for (let i = 0; i <= segments; i++) {
    const angle = (i / segments) * Math.PI * 2 - Math.PI / 2;
    const r = 50 + SEAL_NOISE[i % segments];
    const x = 50 + r * Math.cos(angle);
    const y = 50 + r * Math.sin(angle);

    if (i === 0) {
      d += `M ${x.toFixed(2)} ${y.toFixed(2)}`;
    } else {
      const prevAngle = ((i - 1) / segments) * Math.PI * 2 - Math.PI / 2;
      const prevR = 50 + SEAL_NOISE[(i - 1) % segments];
      const cx = 50 + ((prevR + r) / 2) * Math.cos((prevAngle + angle) / 2);
      const cy = 50 + ((prevR + r) / 2) * Math.sin((prevAngle + angle) / 2);

      d += ` Q ${cx.toFixed(2)} ${cy.toFixed(2)} ${x.toFixed(2)} ${y.toFixed(2)}`;
    }
  }

  return `${d} Z`;
}

function RoyalSeal() {
  const sealPath = useMemo(buildSealPath, []);

  return (
    <svg
      viewBox="0 0 100 100"
      className="royal-seal"
      aria-hidden="true"
    >
      <defs>
        <radialGradient id="royalSealSheen" cx="0.35" cy="0.28" r="0.85">
          <stop offset="0%" stopColor="#efe0b4" stopOpacity="0.95" />
          <stop offset="42%" stopColor="#c5a059" stopOpacity="0.1" />
          <stop offset="100%" stopColor="#b89047" stopOpacity="0" />
        </radialGradient>
      </defs>
      <path d={sealPath} fill="#c5a059" />
      <path
        d={sealPath}
        fill="url(#royalSealSheen)"
        opacity="0.9"
      />
      <path
        d={sealPath}
        fill="none"
        stroke="#a8853f"
        strokeWidth="0.75"
        opacity="0.55"
      />
      <circle cx="50" cy="50" r="35" fill="none" stroke="#a8853f" strokeWidth="1.4" opacity="0.6" />
      <circle cx="50" cy="50" r="33" fill="none" stroke="#a8853f" strokeWidth="0.5" opacity="0.5" />
      <g fill="none" stroke="#8f6a2f" strokeWidth="1.15" strokeLinecap="round" strokeLinejoin="round">
        <path d="M50 34 C57 30 63 35 59 42 C56 47 48 47 44 42 C41 36 46 29 50 30" />
        <path d="M50 40 C55 39 58 43 55 47 C53 50 48 49 46 46 C44 43 47 40 50 40" />
        <path d="M50 46 C52.5 45.8 53.5 47.5 52 49.5 C50.5 51 48.5 50.5 48 49 C47.5 47.5 48 46.2 50 46 Z" />
        <path d="M44 38 C40 41 38 45 41.5 47 C45 49 47.5 46 46 42.5" />
        <path d="M56 38 C60 41 61 46 57.5 47.5 C54 49 51.5 45.5 53 42" />
      </g>
      <ellipse
        cx="50"
        cy="66"
        rx="6"
        ry="1.8"
        fill="none"
        stroke="#8f6a2f"
        strokeWidth="1.1"
        opacity="0.6"
        transform="rotate(-6 50 66)"
      />
    </svg>
  );
}

function Vine({ className = "" }) {
  const leaves = [
    { transform: "translate(160 28) rotate(-35)" },
    { transform: "translate(150 68) rotate(35)" },
    { transform: "translate(130 108) rotate(-40)" },
    { transform: "translate(92 134) rotate(30)" },
    { transform: "translate(50 158) rotate(-35)" },
    { transform: "translate(20 190) rotate(42)" },
  ];

  return (
    <svg
      viewBox="0 0 180 220"
      fill="none"
      className={`vine${className ? ` ${className}` : ""}`}
      aria-hidden="true"
    >
      <path
        d="M172 8 C 142 46, 150 92, 114 118 S 56 150, 8 212"
        strokeWidth="1.6"
        strokeLinecap="round"
      />
      {leaves.map((leaf, index) => (
        <g
          key={index}
          className="vine__leaf"
          transform={leaf.transform}
        >
          <path d="M-26 0 C -16 -12, 8 -14, 26 0 C 8 14, -16 12, -26 0 Z" />
          <path d="M-22 0 L 20 0" strokeWidth="0.8" />
        </g>
      ))}
    </svg>
  );
}

function Reveal({ children, className = "", delay = 0 }) {
  const ref = useRef(null);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setVisible(true);
          observer.disconnect();
        }
      },
      { threshold: 0.12, rootMargin: "0px 0px -30px 0px" },
    );

    observer.observe(el);

    return () => observer.disconnect();
  }, []);

  return (
    <div
      ref={ref}
      className={`reveal${visible ? " reveal--visible" : ""}${className ? ` ${className}` : ""}`}
      style={delay ? { transitionDelay: `${delay}ms` } : undefined}
    >
      {children}
    </div>
  );
}

function Countdown({ target }) {
  const calculate = () => {
    const diff = Math.max(0, target.getTime() - Date.now());

    return {
      days: Math.floor(diff / 86400000),
      hours: Math.floor(diff / 3600000) % 24,
      minutes: Math.floor(diff / 60000) % 60,
      seconds: Math.floor(diff / 1000) % 60,
    };
  };

  const [fields, setFields] = useState(calculate);

  useEffect(() => {
    const id = window.setInterval(() => setFields(calculate()), 1000);
    return () => window.clearInterval(id);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const items = [
    { label: "Days", value: fields.days },
    { label: "Hours", value: fields.hours },
    { label: "Minutes", value: fields.minutes },
    { label: "Seconds", value: fields.seconds },
  ];

  return (
    <div className="countdown">
      {items.map((item) => (
        <div key={item.label} className="countdown__field">
          <span className="serif-display countdown__value">
            {String(item.value).padStart(2, "0")}
          </span>
          <span className="countdown__label">{item.label}</span>
        </div>
      ))}
    </div>
  );
}

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

const mdashFix = (value) => String(value ?? "").replace(/—/g, "-");

const formatDate = (value) => {
  if (!value) return "";

  const [year, month, day] = String(value).slice(0, 10).split("-");
  if (!year || !month || !day) return "";

  return new Date(year, Number(month) - 1, day).toLocaleDateString(undefined, {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const formatTime = (value) => {
  if (!value) return "";

  const [hour, minute] = String(value).split(":");
  const h = Number(hour);
  const suffix = h >= 12 ? "pm" : "am";
  const display = h % 12 === 0 ? 12 : h % 12;

  return `${display}:${minute} ${suffix}`;
};

export default function Royal({ invitation, wishes = [] }) {
  const { template, melody, events = [], photos = [] } = invitation;

  const introSrc = template?.intro_video_path ? `/${template.intro_video_path}` : null;
  const melodySrc = melody?.file_path ? `/${melody.file_path}` : null;

  const videoRef = useRef(null);
  const audioRef = useRef(null);

  const [phase, setPhase] = useState("paused");
  const [musicPlaying, setMusicPlaying] = useState(false);
  const [introFailed, setIntroFailed] = useState(false);

  const showIntro = Boolean(introSrc) && !introFailed;

  const targetDate = useMemo(
    () => new Date(`${String(invitation.event_date).slice(0, 10)}T${invitation.event_time}:00`),
    [invitation.event_date, invitation.event_time],
  );

  useEffect(() => {
    if (showIntro && phase !== "done") {
      document.body.style.overflow = "hidden";
      return () => {
        document.body.style.overflow = "";
      };
    }
  }, [showIntro, phase]);

  const start = () => {
    if (phase !== "paused") return;

    setPhase("playing");
    videoRef.current?.play();
    audioRef.current?.play().catch(() => {});
    setMusicPlaying(true);
  };

  const handleVideoEnd = () => {
    setPhase("fading");
    window.setTimeout(() => setPhase("done"), INTRO_FADE_MS);
  };

  const toggleMusic = () => {
    const audio = audioRef.current;
    if (!audio) return;

    if (musicPlaying) {
      audio.pause();
      setMusicPlaying(false);
    } else {
      audio.play().catch(() => {});
      setMusicPlaying(true);
    }
  };

  const groomName = mdashFix(invitation.groom_name);
  const brideName = mdashFix(invitation.bride_name);
  const coupleTitle = `${groomName} & ${brideName}`;
  const venueAddress = invitation.venue_address
    ? mdashFix(invitation.venue_address)
    : null;
  const mapHref = venueAddress
    ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(venueAddress)}`
    : null;
  const hasContact = Boolean(invitation.contact_phone);

  return (
    <>
      <Head title={coupleTitle}>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
          rel="stylesheet"
          href="https://fonts.bunny.net/css?family=cinzel:400,500,600,700&family=cormorant-garamond:400,500,600&family=great-vibes:400&display=swap"
        />
      </Head>

      {melodySrc && (
        <audio ref={audioRef} src={melodySrc} loop preload="auto" />
      )}

      {showIntro && phase !== "done" && (
        <div
          className={`intro-overlay${phase === "fading" ? " intro-overlay--fading" : ""}`}
          onClick={start}
        >
          <video
            ref={videoRef}
            src={introSrc}
            muted
            playsInline
            preload="auto"
            onEnded={handleVideoEnd}
            onError={() => setIntroFailed(true)}
          />

          {phase === "paused" && (
            <div className="intro-overlay__hint">
              <p>Tap to open your invitation</p>
            </div>
          )}
        </div>
      )}

      <div className="invitation-page">
        <Vine className="vine--corner-tl" />
        <Vine className="vine--corner-br" />

        <div className="invitation-page__inner">
          <header className="hero">
            <Reveal>
              <div className="hero__seal">
                <RoyalSeal />
              </div>
              <p className="hero__overline">Together with love</p>
              <h1 className="serif-display hero__names">
                {groomName}
                <span className="hero__amp">&</span>
                {brideName}
              </h1>
              <p className="script-accent hero__script">are getting married</p>
              <div className="hero__date">
                <time dateTime={String(invitation.event_date).slice(0, 10)}>
                  {formatDate(invitation.event_date)}
                </time>
                <span className="hero__date-bullet">·</span>
                <time dateTime={String(invitation.event_time)}>
                  {formatTime(invitation.event_time)}
                </time>
              </div>
            </Reveal>
          </header>

          <Reveal>
            <section className="card">
              <p className="card__eyebrow">Welcome</p>
              <h2 className="serif-display card__title">You are invited</h2>
              <p className="script-accent welcome__invite-names">
                {groomName} & {brideName}
              </p>
              <p className="serif-display card__text card__text--lead welcome__message">
                together with their families and friends, joyfully invite you to
                celebrate their wedding day with them.
              </p>
            </section>
          </Reveal>

          <Reveal>
            <section className="card">
              <p className="card__eyebrow">The big day</p>
              <h2 className="serif-display card__title">Counting down</h2>
              <p className="card__subtitle">
                Save the date - {formatDate(invitation.event_date)}
              </p>
              <Countdown target={targetDate} />
            </section>
          </Reveal>

          <Reveal>
            <section className="card">
              <p className="card__eyebrow">Schedule</p>
              <h2 className="serif-display card__title">Timeline of the day</h2>
              <ol className="timeline">
                {events.map((event) => (
                  <li key={event.id} className="timeline__item">
                    <span className="timeline__time">{formatTime(event.time)}</span>
                    <span className="timeline__marker" />
                    <span className="timeline__name">{event.name}</span>
                  </li>
                ))}
              </ol>
            </section>
          </Reveal>

          {mapHref && (
            <Reveal>
              <section className="card">
                <p className="card__eyebrow">Location</p>
                <h2 className="serif-display card__title">
                  {mdashFix(invitation.venue_name)}
                </h2>
                <p className="card__address">{venueAddress}</p>
                <a
                  href={mapHref}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="map-link"
                >
                  <MapPin className="map-link__icon" aria-hidden="true" />
                  <span>View on map</span>
                </a>
              </section>
            </Reveal>
          )}

          {photos.length > 0 && (
            <Reveal>
              <section className="card">
                <p className="card__eyebrow">Gallery</p>
                <h2 className="serif-display card__title">Our Moments</h2>
                <div className="gallery">
                  {photos.map((photo) => (
                    <figure key={photo.id} className="gallery__item">
                      <img
                        src={`/storage/${photo.photo_path}`}
                        alt={`${coupleTitle} wedding`}
                        loading="lazy"
                      />
                    </figure>
                  ))}
                </div>
              </section>
            </Reveal>
          )}

          {invitation.note && (
            <Reveal>
              <section className="card">
                <p className="card__eyebrow">A note</p>
                <p className="card__text">{mdashFix(invitation.note)}</p>
              </section>
            </Reveal>
          )}

          {hasContact && (
            <Reveal>
              <section className="card">
                <p className="card__eyebrow">Contact</p>
                <h2 className="serif-display card__title">Reach out</h2>
                <p className="card__text">
                  For any questions about our special day, please reach us at:
                </p>
                <div className="contact-card">
                  {invitation.contact_phone && (
                    <a
                      className="contact-card__item"
                      href={`tel:${invitation.contact_phone}`}
                    >
                      {invitation.contact_phone}
                    </a>
                  )}
                </div>
              </section>
            </Reveal>
          )}

          <Reveal>
            <RsvpCard invitation={invitation} wishes={wishes} />
          </Reveal>

          <Reveal>
            <footer className="footer">
              <Link href={route("home")}>
                Crafted with care · {appName}
              </Link>
            </footer>
          </Reveal>
        </div>
      </div>

      {melodySrc && (
        <button
          type="button"
          className="music-toggle"
          onClick={toggleMusic}
          aria-label={musicPlaying ? "Pause music" : "Play music"}
        >
          {musicPlaying ? <Pause /> : <Music />}
        </button>
      )}
    </>
  );
}