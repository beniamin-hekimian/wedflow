import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { User } from 'lucide-react';

import ApplicationLogo from '@/Components/ApplicationLogo';
import { Button } from '@/components/ui/button';

export default function Navbar() {
  const user = usePage().props.auth.user;
  const [open, setOpen] = useState(false);

  const links = [
    {
      name: 'Home',
      href: route('home'),
      active: route().current('home'),
    },
    {
      name: 'Templates',
      href: route('templates.index'),
      active: route().current('templates.index'),
    },
  ];

  const invitationLinks = [
    {
      name: 'Invitations',
      href: route('invitations.index'),
      active: route().current('invitations.index'),
    },
  ];

  const adminLinks = [
    {
      name: 'Dashboard',
      href: route('admin.dashboard'),
      active: route().current('admin.dashboard'),
    },
  ];

  const navLinkClass = (active) =>
    `rounded-md px-3 py-2 text-sm font-medium transition ${
      active
        ? 'bg-primary/10 text-primary'
        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
    }`;

  return (
    <nav className="sticky top-0 z-50 border-b border-border bg-background">
      <div className="relative mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div className="flex items-center gap-2">
          <Link href="/" className="flex items-center gap-2">
            <ApplicationLogo className="block h-9 w-auto" />
            <span className="font-display text-lg font-semibold text-foreground">
              Wedflow
            </span>
          </Link>
        </div>

        <div className="absolute left-1/2 hidden -translate-x-1/2 items-center gap-1 md:flex">
          {links.map((link) => (
            <Link key={link.name} href={link.href} className={navLinkClass(link.active)}>
              {link.name}
            </Link>
          ))}

          {invitationLinks.map((link) => (
            <Link key={link.name} href={link.href} className={navLinkClass(link.active)}>
              {link.name}
            </Link>
          ))}

          {user?.role === 'admin' &&
            adminLinks.map((link) => (
              <Link key={link.name} href={link.href} className={navLinkClass(link.active)}>
                {link.name}
              </Link>
            ))}
        </div>

        <div className="hidden items-center gap-3 md:flex">
          {user ? (
            <>
              <Link href={route('profile.edit')}>
                <Button variant="ghost" size="icon" aria-label="Profile">
                  <User className="size-5" />
                </Button>
              </Link>
              <Link href={route('logout')} method="post">
                <Button
                  variant="ghost"
                  size="sm"
                  className="text-destructive hover:text-destructive"
                >
                  Log Out
                </Button>
              </Link>
            </>
          ) : (
            <>
              <Link href={route('login')}>
                <Button variant="ghost" size="sm">
                  Log in
                </Button>
              </Link>
              <Link href={route('register')}>
                <Button size="sm">Sign Up</Button>
              </Link>
            </>
          )}
        </div>

        <button
          onClick={() => setOpen((prev) => !prev)}
          className="inline-flex items-center justify-center rounded-md p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground md:hidden"
          aria-label="Toggle navigation"
        >
          <svg
            className="h-6 w-6"
            stroke="currentColor"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              className={!open ? 'inline-flex' : 'hidden'}
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
            <path
              className={open ? 'inline-flex' : 'hidden'}
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      {open && (
        <div className="space-y-1 border-t border-border px-4 pb-3 pt-2 md:hidden">
          {links.map((link) => (
            <Link
              key={link.name}
              href={link.href}
              className={navLinkClass(link.active) + ' block'}
            >
              {link.name}
            </Link>
          ))}

          {invitationLinks.map((link) => (
            <Link
              key={link.name}
              href={link.href}
              className={navLinkClass(link.active) + ' block'}
            >
              {link.name}
            </Link>
          ))}

          {user?.role === 'admin' &&
            adminLinks.map((link) => (
              <Link
                key={link.name}
                href={link.href}
                className={navLinkClass(link.active) + ' block'}
              >
                {link.name}
              </Link>
            ))}

          <div className="border-t border-border pt-3">
            {user ? (
              <>
                <Link href={route('profile.edit')}>
                  <Button
                    variant="ghost"
                    size="sm"
                    className="w-full justify-start"
                    aria-label="Profile"
                  >
                    <User className="size-5" />
                  </Button>
                </Link>
                <Link href={route('logout')} method="post" className="mt-2 block">
                  <Button
                    variant="ghost"
                    size="sm"
                    className="w-full justify-start text-destructive hover:text-destructive"
                  >
                    Log Out
                  </Button>
                </Link>
              </>
            ) : (
              <>
                <Link href={route('login')}>
                  <Button variant="ghost" size="sm" className="w-full justify-start">
                    Log in
                  </Button>
                </Link>
                <Link href={route('register')} className="mt-2 block">
                  <Button size="sm" className="w-full justify-start">
                    Sign Up
                  </Button>
                </Link>
              </>
            )}
          </div>
        </div>
      )}
    </nav>
  );
}