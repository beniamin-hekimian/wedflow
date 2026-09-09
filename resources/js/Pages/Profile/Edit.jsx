import { Head } from '@inertiajs/react';

import Navbar from '@/Components/Navbar';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({ mustVerifyEmail, status }) {
  return (
    <div className="flex min-h-screen flex-col bg-background">
      <Head title="Profile" />
      <Navbar />

      <main className="flex-1">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
          <h1 className="mb-8 font-display text-3xl font-semibold leading-tight text-foreground">
            Profile
          </h1>

          <div className="space-y-6">
            <div className="bg-card p-4 shadow-sm sm:rounded-lg sm:p-8">
              <UpdateProfileInformationForm
                mustVerifyEmail={mustVerifyEmail}
                status={status}
                className="max-w-xl"
              />
            </div>

            <div className="bg-card p-4 shadow-sm sm:rounded-lg sm:p-8">
              <UpdatePasswordForm className="max-w-xl" />
            </div>

            <div className="bg-card p-4 shadow-sm sm:rounded-lg sm:p-8">
              <DeleteUserForm className="max-w-xl" />
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}