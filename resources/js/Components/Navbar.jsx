import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

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

  const authedLinks = [
    {
      name: 'Invitations',
      href: route('invitations.index'),
      active: route().current('invitations.index'),
    },
  ];

  return (
    <nav className="sticky top-0 z-50 border-b border-gray-200 bg-white">
      <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div className="flex items-center gap-6">
          <Link href="/">
            <ApplicationLogo className="block h-9 w-auto fill-current text-gray-800" />
          </Link>

          <div className="hidden items-center gap-1 md:flex">
            {links.map((link) => (
              <Link
                key={link.name}
                href={link.href}
                className={`rounded-md px-3 py-2 text-sm font-medium transition ${
                  link.active
                    ? 'bg-gray-100 text-gray-900'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                }`}
              >
                {link.name}
              </Link>
            ))}

            {user &&
              authedLinks.map((link) => (
                <Link
                  key={link.name}
                  href={link.href}
                  className={`rounded-md px-3 py-2 text-sm font-medium transition ${
                    link.active
                      ? 'bg-gray-100 text-gray-900'
                      : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                  }`}
                >
                  {link.name}
                </Link>
              ))}
          </div>
        </div>

        <div className="hidden items-center gap-3 md:flex">
          {user ? (
            <>
              <Link href={route('profile.edit')}>
                <Button variant="ghost" size="sm">
                  Profile
                </Button>
              </Link>
              <Link href={route('logout')} method="post">
                <Button variant="outline" size="sm">
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
          className="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 md:hidden"
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
        <div className="space-y-1 border-t border-gray-200 px-4 pb-3 pt-2 md:hidden">
          {links.map((link) => (
            <Link
              key={link.name}
              href={link.href}
              className={`block rounded-md px-3 py-2 text-sm font-medium transition ${
                link.active
                  ? 'bg-gray-100 text-gray-900'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
              }`}
            >
              {link.name}
            </Link>
          ))}

          {user &&
            authedLinks.map((link) => (
              <Link
                key={link.name}
                href={link.href}
                className={`block rounded-md px-3 py-2 text-sm font-medium transition ${
                  link.active
                    ? 'bg-gray-100 text-gray-900'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                }`}
              >
                {link.name}
              </Link>
            ))}

          <div className="border-t border-gray-200 pt-3">
            {user ? (
              <>
                <Link href={route('profile.edit')}>
                  <Button variant="ghost" size="sm" className="w-full justify-start">
                    Profile
                  </Button>
                </Link>
                <Link href={route('logout')} method="post" className="mt-2 block">
                  <Button variant="outline" size="sm" className="w-full justify-start">
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