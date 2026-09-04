import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ template }) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Create Invitation</h2>}>
            <Head title="Create Invitation" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <p>You selected the "{template.name}" template. The invitation form is coming soon.</p>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
