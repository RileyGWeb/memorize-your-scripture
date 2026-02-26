<x-layouts.app>
    <div class="py-8 w-full">
        <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
                <p class="text-sm text-gray-500 mb-6">Last updated: February 26, 2026</p>

                <div class="space-y-6 text-gray-700">
                    <section>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Overview</h2>
                        <p>
                            Memorize Your Scripture helps you memorize Bible verses and optionally save progress to your account.
                            This page explains what data is collected and how it is used.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Data We Collect</h2>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Account data you provide (such as name and email).</li>
                            <li>App usage and memorization progress needed to provide core features.</li>
                            <li>Technical logs required for reliability and security.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">How We Use Data</h2>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>To create and manage your account.</li>
                            <li>To save and restore your memory bank and quiz-related progress.</li>
                            <li>To maintain app performance, prevent abuse, and troubleshoot issues.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Data Sharing</h2>
                        <p>
                            We do not sell your personal information. Data may be processed by service providers needed to operate
                            core app functionality.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Your Choices</h2>
                        <p class="mb-2">
                            You can access and update profile information from your account settings.
                        </p>
                        <p>
                            If you want your account and associated data deleted, use the account deletion option in your profile settings,
                            or contact us through the <a href="{{ route('contact') }}" class="underline" wire:navigate>Contact page</a>.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Contact</h2>
                        <p>
                            For privacy questions or deletion requests, use the
                            <a href="{{ route('contact') }}" class="underline" wire:navigate>Contact page</a>.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
