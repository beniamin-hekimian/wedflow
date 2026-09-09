import { Head, Link, usePage } from "@inertiajs/react";
import { useEffect, useRef, useState } from "react";
import { LayoutTemplate, PenLine, Stamp } from "lucide-react";

import Navbar from "@/Components/Navbar";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

const GRAIN =
  "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E\")";

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
      className={`transition-all duration-700 ease-out ${
        visible ? "translate-y-0 opacity-100" : "translate-y-6 opacity-0"
      }${className ? ` ${className}` : ""}`}
      style={delay ? { transitionDelay: `${delay}ms` } : undefined}
    >
      {children}
    </div>
  );
}

export default function Home({ templates = [] }) {
  const user = usePage().props.auth.user;

  const steps = [
    {
      icon: LayoutTemplate,
      title: "Choose a design",
      text: "Pick the template that feels like your story - Classic, Royal, or Majestic.",
    },
    {
      icon: PenLine,
      title: "Tell your story",
      text: "Add your names, venue, timeline, gallery, and a personal note.",
    },
    {
      icon: Stamp,
      title: "Seal it and share",
      text: "Publish a private invitation with RSVP and wishes from your guests.",
    },
  ];

  return (
    <>
      <Head title="Home" />

      <div className="relative flex min-h-screen flex-col overflow-hidden bg-wax-ivory">
        <div
          className="pointer-events-none absolute inset-0"
          style={{ backgroundImage: GRAIN }}
          aria-hidden="true"
        />

        <Navbar />

        <main className="relative flex-1">
          <section className="mx-auto max-w-7xl px-4 pt-20 pb-20 text-center sm:px-6 lg:px-8">
            <Reveal>
              <div className="mx-auto w-fit rounded-full border border-wax-gold/25 bg-wax-card p-2 shadow-[0_22px_44px_-24px_rgba(150,115,60,0.55)]">
                <img
                  src="/favicon.png"
                  alt="Wedflow seal"
                  className="h-24 w-24 rounded-full object-cover"
                />
              </div>
            </Reveal>

            <Reveal delay={100}>
              <p className="mt-10 text-xs font-semibold uppercase tracking-[0.32em] text-wax-gold">
                Wedflow
              </p>
              <h1 className="mx-auto mt-5 max-w-3xl font-display text-5xl font-semibold leading-[1.05] tracking-tight text-wax-ink sm:text-6xl">
                Your love story,{" "}
                <em className="italic text-wax-goldDeep">sealed.</em>
              </h1>
              <p className="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-wax-inkSoft">
                Choose a handcrafted design, add your details, and share a
                private invitation with your guests - complete with RSVP and
                wedding wishes.
              </p>
            </Reveal>

            <Reveal delay={200}>
              <div className="mt-9 flex flex-wrap items-center justify-center gap-3">
                <Link href={route("templates.index")}>
                  <Button
                    size="lg"
                    className="bg-wax-gold text-white hover:bg-wax-goldDeep"
                  >
                    Browse templates
                  </Button>
                </Link>
                {user ? (
                  <Link href={route("invitations.index")}>
                    <Button variant="outline" size="lg">
                      My invitations
                    </Button>
                  </Link>
                ) : (
                  <Link href={route("register")}>
                    <Button variant="outline" size="lg">
                      Create your invitation
                    </Button>
                  </Link>
                )}
              </div>

              <p className="mt-9 text-sm font-medium tracking-wide text-wax-inkFaint">
                <span>3 handcrafted designs</span>
                <span className="mx-3 text-wax-gold">·</span>
                <span>Free RSVP page</span>
                <span className="mx-3 text-wax-gold">·</span>
                <span>Private invite link</span>
              </p>
            </Reveal>
          </section>

          <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <Reveal>
              <div className="text-center">
                <p className="text-xs font-semibold uppercase tracking-[0.32em] text-wax-gold">
                  Templates
                </p>
                <h2 className="mt-3 font-display text-3xl font-semibold tracking-tight text-wax-ink sm:text-4xl">
                  Designed to be remembered
                </h2>
                <p className="mx-auto mt-3 max-w-lg text-wax-inkSoft">
                  Three timeless styles - from classic elegance to royal
                  splendor. Start from any design you love.
                </p>
              </div>
            </Reveal>

            <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {templates.map((template, index) => (
                <Reveal
                  key={template.id}
                  className="h-full"
                  delay={index * 100}
                >
                  <Card className="group h-full overflow-hidden">
                    <div className="aspect-[3/4] overflow-hidden bg-wax-cream">
                      <img
                        src={`/${template.thumbnail_path}`}
                        alt={`${template.name} template`}
                        loading="lazy"
                        className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                      />
                    </div>
                    <CardHeader>
                      <CardTitle className="font-display text-lg font-semibold text-wax-ink">
                        {template.name}
                      </CardTitle>
                      <CardDescription>{template.description}</CardDescription>
                    </CardHeader>
                    <CardContent>
                      <Link
                        href={route("invitations.create", {
                          template: template.id,
                        })}
                      >
                        <Button className="w-full bg-wax-gold text-white hover:bg-wax-goldDeep">
                          Prepare your invitation
                        </Button>
                      </Link>
                    </CardContent>
                  </Card>
                </Reveal>
              ))}
            </div>
          </section>

          <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <Reveal>
              <div className="text-center">
                <p className="text-xs font-semibold uppercase tracking-[0.32em] text-wax-gold">
                  How it works
                </p>
                <h2 className="mt-3 font-display text-3xl font-semibold tracking-tight text-wax-ink sm:text-4xl">
                  Sealed in three steps
                </h2>
              </div>
            </Reveal>

            <div className="mt-12 grid gap-6 sm:grid-cols-3">
              {steps.map(({ icon: Icon, title, text }, index) => (
                <Reveal key={title} className="h-full" delay={index * 100}>
                  <Card className="h-full text-center">
                    <CardHeader>
                      <div className="mx-auto grid h-14 w-14 place-items-center rounded-full border border-wax-gold/30 text-wax-goldDeep">
                        <Icon className="h-6 w-6" aria-hidden="true" />
                      </div>
                      <CardTitle className="mt-3 font-display text-xl font-semibold text-wax-ink">
                        {title}
                      </CardTitle>
                      <CardDescription>{text}</CardDescription>
                    </CardHeader>
                  </Card>
                </Reveal>
              ))}
            </div>
          </section>

          <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <Reveal>
              <div className="rounded-2xl border border-wax-gold/20 bg-wax-cream px-8 py-14 text-center">
                <h2 className="font-display text-3xl font-semibold tracking-tight text-wax-ink sm:text-4xl">
                  Ready to <em className="italic text-wax-goldDeep">begin?</em>
                </h2>
                <p className="mx-auto mt-3 max-w-md text-wax-inkSoft">
                  Your guests are waiting to celebrate with you. Start crafting
                  a keepsake they will keep forever.
                </p>
                <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                  {user ? (
                    <Link href={route("invitations.create")}>
                      <Button
                        size="lg"
                        className="bg-wax-gold text-white hover:bg-wax-goldDeep"
                      >
                        Create an invitation
                      </Button>
                    </Link>
                  ) : (
                    <Link href={route("register")}>
                      <Button
                        size="lg"
                        className="bg-wax-gold text-white hover:bg-wax-goldDeep"
                      >
                        Get started free
                      </Button>
                    </Link>
                  )}
                </div>
              </div>
            </Reveal>
          </section>
        </main>

        <footer className="relative border-t border-wax-gold/15 bg-wax-card">
          <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 sm:flex-row sm:px-6 lg:px-8">
            <div className="flex items-center gap-2">
              <img src="/favicon.png" alt="" className="h-8 w-8 rounded-full" />
              <span className="font-display text-lg font-semibold text-wax-ink">
                Wedflow
              </span>
            </div>
            <p className="text-sm text-wax-inkFaint">
              Crafted with care · Digital wedding invitations
            </p>
            <nav className="flex items-center gap-5 text-sm text-wax-inkSoft">
              <Link
                href={route("home")}
                className="transition hover:text-wax-goldDeep"
              >
                Home
              </Link>
              <Link
                href={route("templates.index")}
                className="transition hover:text-wax-goldDeep"
              >
                Templates
              </Link>
              {user ? (
                <Link
                  href={route("invitations.index")}
                  className="transition hover:text-wax-goldDeep"
                >
                  My invitations
                </Link>
              ) : (
                <Link
                  href={route("register")}
                  className="transition hover:text-wax-goldDeep"
                >
                  Get started
                </Link>
              )}
            </nav>
          </div>
        </footer>
      </div>
    </>
  );
}
