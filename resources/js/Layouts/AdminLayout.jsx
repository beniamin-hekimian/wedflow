import { Link, usePage } from '@inertiajs/react';

import Navbar from '@/Components/Navbar';
import { Button } from '@/components/ui/button';

export default function AdminLayout({ children }) {
  const user = usePage().props.auth.user;

  const navItems = [
    {
      name: 'Dashboard',
      href: route('admin.dashboard'),
      active: route().current('admin.dashboard'),
    },
    {
      name: 'Invitations',
      href: route('admin.invitations.index'),
      active: route().current('admin.invitations*'),
    },
  ];

  return (
    <div className="flex min-h-screen flex-col bg-gray-100">
      <Navbar />

      <div className="flex flex-1">
        <aside className="hidden w-64 shrink-0 flex-col border-r border-gray-200 bg-white md:flex">
          <div className="px-4 py-5">
            <p className="text-xs font-semibold uppercase tracking-wider text-gray-400">
              Admin Panel
            </p>
          </div>

          <nav className="flex-1 space-y-1 px-3">
            {navItems.map((item) => (
              <Link
                key={item.name}
                href={item.href}
                className={`block rounded-md px-3 py-2 text-sm font-medium transition ${
                  item.active
                    ? 'bg-gray-200 text-gray-900'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                }`}
              >
                {item.name}
              </Link>
            ))}
          </nav>

          <div className="space-y-2 border-t border-gray-200 p-4">
            <Link href={route('home')}>
              <Button variant="ghost" size="sm" className="w-full justify-start">
                Back to site
              </Button>
            </Link>
            <Link href={route('logout')} method="post">
              <Button variant="outline" size="sm" className="w-full justify-start">
                Log Out
              </Button>
            </Link>
          </div>
        </aside>

        <main className="flex-1">
          <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
}