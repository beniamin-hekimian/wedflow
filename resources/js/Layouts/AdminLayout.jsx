import { Link, usePage } from '@inertiajs/react';
import {
  LayoutDashboard,
  LayoutTemplate,
  Mail,
  MessageSquareText,
  Music,
  Users,
} from 'lucide-react';

import Navbar from '@/Components/Navbar';
import { Button } from '@/components/ui/button';

export default function AdminLayout({ children }) {
  const user = usePage().props.auth.user;

  const navItems = [
    {
      name: 'Dashboard',
      href: route('admin.dashboard'),
      active: route().current('admin.dashboard'),
      icon: LayoutDashboard,
    },
    {
      name: 'Users',
      href: route('admin.users.index'),
      active: route().current('admin.users*'),
      icon: Users,
    },
    {
      name: 'Templates',
      href: route('admin.templates.index'),
      active: route().current('admin.templates*'),
      icon: LayoutTemplate,
    },
    {
      name: 'Melodies',
      href: route('admin.melodies.index'),
      active: route().current('admin.melodies*'),
      icon: Music,
    },
    {
      name: 'Invitations',
      href: route('admin.invitations.index'),
      active: route().current('admin.invitations*'),
      icon: Mail,
    },
    {
      name: 'Responses',
      href: route('admin.responses.index'),
      active: route().current('admin.responses*'),
      icon: MessageSquareText,
    },
  ];

  return (
    <div className="flex min-h-screen flex-col bg-background">
      <Navbar />

      <div className="flex flex-1">
        <aside className="hidden w-64 shrink-0 flex-col border-r border-border bg-card md:flex">
          <div className="px-4 py-5">
            <p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Admin Panel
            </p>
          </div>

          <nav className="flex-1 space-y-1 px-3">
            {navItems.map((item) => (
              <Link
                key={item.name}
                href={item.href}
                className={`flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition ${
                  item.active
                    ? 'bg-primary/10 text-primary'
                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                }`}
              >
                <item.icon className="size-4 shrink-0" />
                {item.name}
              </Link>
            ))}
          </nav>

          <div className="space-y-2 border-t border-border p-4">
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