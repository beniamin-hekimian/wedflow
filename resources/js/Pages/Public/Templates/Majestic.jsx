import { Head, Link } from "@inertiajs/react";
import { useEffect, useMemo, useRef, useState } from "react";
import { MapPin, Music, Pause } from "lucide-react";

import RsvpCard from "../Components/RsvpCard";
import PreviewBanner from "../Components/PreviewBanner";
import "../../../../css/invitations/majestic.css";

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

function MajesticSeal() {
  const sealPath = useMemo(buildSealPath, []);

  return (
    <svg
      viewBox="0 0 110 110"
      className="majestic-seal"
      aria-hidden="true"
    >
      <defs>
        <radialGradient id="majesticSealGrad" cx="0.38" cy="0.32" r="0.95">
          <stop offset="0%" stopColor="#f2c6c6" />
          <stop offset="45%" stopColor="#e5a9a9" />
          <stop offset="80%" stopColor="#d49292" />
          <stop offset="100%" stopColor="#c87d8a" />
        </radialGradient>
        <radialGradient id="majesticSealSheen" cx="0.35" cy="0.28" r="0.9">
          <stop offset="0%" stopColor="#fce8e8" stopOpacity="0.95" />
          <stop offset="45%" stopColor="#e5a9a9" stopOpacity="0.18" />
          <stop offset="100%" stopColor="#d49292" stopOpacity="0" />
        </radialGradient>
      </defs>

      <path d={sealPath} transform="translate(5 5)" fill="url(#majesticSealGrad)" />
      <path
        d={sealPath}
        transform="translate(5 5)"
        fill="url(#majesticSealSheen)"
        opacity="0.85"
      />
      <path
        d={sealPath}
        transform="translate(5 5)"
        fill="none"
        stroke="#b9717c"
        strokeWidth="0.9"
        opacity="0.5"
      />
      <circle cx="55" cy="55" r="43" fill="none" stroke="#c98a92" strokeWidth="1.2" opacity="0.55" />
      <circle cx="55" cy="55" r="40" fill="none" stroke="#c98a92" strokeWidth="0.5" opacity="0.45" />
      <g
        fill="none"
        stroke="#bf6f78"
        strokeWidth="1.1"
        strokeLinecap="round"
        strokeLinejoin="round"
        opacity="0.85"
        transform="translate(-5.5 -14.3) scale(0.55)"
      >
        <path d="M110 136 C 118 131 126 127 122 118 C 119 111 109 108 104 113 C 100 118 104 134 110 136" />
        <path d="M110 129 C 115 128 119 131 116 135 C 114 139 107 139 104 135 C 102 131 105 127 110 129" />
        <path d="M110 121 C 112 120 114 122 113 124 C 112 126 110 126 109 125 C 108 124 108 122 110 121 Z" />
        <path d="M104 116 C 99 119 97 124 101 127 C 105 130 108 127 107 123 C 106 119 107 116 104 116" />
        <path d="M116 117 C 122 118 124 123 120 127 C 116 131 112 128 113 124 C 113 120 113 116 116 117" />
        <path d="M104 134 C 100 138 98 143 102 145 C 107 147 109 143 108 140 C 106 137 106 134 104 134" />
        <path d="M116 135 C 121 137 122 142 118 145 C 114 147 112 143 113 140 C 113 137 114 135 116 135" />
      </g>
      <ellipse
        cx="70"
        cy="34"
        rx="9"
        ry="4"
        fill="#ffffff"
        opacity="0.4"
        transform="rotate(-24 70 34)"
      />
      <circle cx="30" cy="42" r="1.1" fill="#d4af37" opacity="0.55" />
      <circle cx="78" cy="70" r="1.3" fill="#d4af37" opacity="0.45" />
      <circle cx="40" cy="82" r="1" fill="#d4af37" opacity="0.5" />
      <circle cx="84" cy="84" r="0.8" fill="#d4af37" opacity="0.4" />
    </svg>
  );
}

function BlushSprig({ className = "" }) {
  return (
    <svg
      viewBox="0 0 170 200"
      fill="none"
      className={`sprig${className ? ` ${className}` : ""}`}
      aria-hidden="true"
    >
      <path
        d="M162 14 C 138 58, 146 96, 104 118 S 48 136, 12 194"
        strokeWidth="1.5"
        strokeLinecap="round"
      />
      <g transform="translate(124 46) rotate(-38)">
        <path d="M-22 0 C -13 -10, 7 -12, 22 0 C 7 12, -13 10, -22 0 Z" />
        <path d="M-18 0 L 16 0" strokeWidth="0.7" />
      </g>
      <g transform="translate(134 80) rotate(32)">
        <path d="M-18 0 C -11 -9, 6 -10, 18 0 C 6 10, -11 9, -18 0 Z" />
        <path d="M-15 0 L 13 0" strokeWidth="0.7" />
      </g>
      <g transform="translate(98 106) rotate(-28)">
        <path d="M-20 0 C -12 -10, 6 -11, 20 0 C 6 11, -12 10, -20 0 Z" />
        <path d="M-17 0 L 15 0" strokeWidth="0.7" />
      </g>
      <g transform="translate(88 138) rotate(34)">
        <path d="M-14 0 C -9 -8, 5 -9, 14 0 C 5 9, -9 8, -14 0 Z" />
        <path d="M-12 0 L 10 0" strokeWidth="0.7" />
      </g>
      <circle cx="142" cy="60" r="1.6" />
      <circle cx="150" cy="72" r="1.2" />
      <circle cx="62" cy="152" r="1.6" />
      <circle cx="72" cy="146" r="1.1" />
      <circle cx="14" cy="178" r="1.4" strokeWidth="1.1" />
      <path
        d="M120 22 C 112 22, 110 30, 116 32 C 112 36, 116 42, 122 38 C 126 42, 132 36, 128 31 C 133 28, 128 21, 120 22 Z"
        strokeWidth="1"
      />
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

export default function Majestic({ invitation, wishes = [], preview = false }) {
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
          href="https://fonts.bunny.net/css?family=montecarlo:400&family=playfair-display:400,500,600&family=cormorant-garamond:400,500,600&display=swap"
        />
      </Head>

      <PreviewBanner preview={preview} status={invitation.status} />

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
        <BlushSprig className="sprig--corner-tl" />
        <BlushSprig className="sprig--corner-br" />

        <div className="invitation-page__inner">
          <header className="hero">
            <Reveal>
              <div className="hero__seal">
                <MajesticSeal />
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

          {!preview && (
            <Reveal>
              <RsvpCard invitation={invitation} wishes={wishes} />
            </Reveal>
          )}

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