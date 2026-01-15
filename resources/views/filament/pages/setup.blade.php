<x-filament-panels::page>
    <div class="prose dark:prose-invert max-w-none">

        <x-filament::section>
            <x-slot name="heading">
                Setup XML-RPC (Supervisor's API)
            </x-slot>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                To enable this plugin to control Supervisor, you must enable the XML-RPC interface in your
                <code>supervisord.conf</code> file.
            </p>

            <div class="bg-gray-900 rounded p-4 mb-4">
                <pre class="text-xs text-gray-300 font-mono">
[inet_http_server]
port=127.0.0.1:9125
username=user
password=strongpass
</pre>
            </div>

            <p class="mb-4">
                Update your Laravel <code>.env</code> file or <code>config/supervisor-manager.php</code> to match these
                credentials.
                Then your Laravel app can call it locally.
            </p>

            <div
                class="flex items-start gap-3 p-4 rounded-lg bg-warning-50 text-warning-950 dark:bg-warning-900/30 dark:text-warning-200 border border-warning-200 dark:border-warning-800">
                <x-heroicon-m-exclamation-triangle class="w-6 h-6 shrink-0" />
                <div class="text-sm">
                    <strong>Security Warning:</strong> This interface grants control over your processes.
                    You must treat it effectively like root access. Do not expose this port beyond localhost
                    (127.0.0.1).
                </div>
            </div>

        </x-filament::section>

        <x-filament::section class="mt-6">
            <x-slot name="heading">
                Permissions & Sudoers
            </x-slot>

            <p class="mb-4">
                If you use the "Deploy" feature, your web server user needs permission to run
                <code>supervisorctl</code>.
            </p>

            <h3 class="font-bold text-lg mb-2">Development (Mac/Local)</h3>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                If your app runs as your logged-in mac user (e.g. via Valet or Herd), you typically don't need
                additional sudo setup,
                as your user likely owns the supervisor config files or has permission to run supervisorctl.
            </p>

            <h3 class="font-bold text-lg mb-2">Production (Linux / www-data)</h3>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                If running as <code>www-data</code>, you will likely need to edit sudoers to allow running config
                updates without a password.
            </p>

            <ol class="list-decimal list-inside space-y-4 mb-4">
                <li>
                    Run this command to edit sudoers:
                    <div x-data="{ copied: false }" class="relative group mt-2">
                        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded font-mono text-sm pr-10">
                            <span x-ref="cmd">sudo visudo</span>
                        </div>
                        <button
                            x-on:click="
                                window.navigator.clipboard.writeText($refs.cmd.innerText);
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            title="Copy to clipboard"
                        >
                            <span x-show="!copied"><x-heroicon-m-clipboard class="w-5 h-5" /></span>
                            <span x-show="copied" x-cloak><x-heroicon-m-check class="w-5 h-5 text-emerald-500" /></span>
                        </button>
                    </div>
                </li>
                <li>
                    Add the following entry (replace <code>www-data</code> with your web user):
                    <div x-data="{ copied: false }" class="relative group mt-2">
                        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded font-mono text-sm pr-10">
                            <span x-ref="cmd">www-data ALL=(root) NOPASSWD: /usr/bin/supervisorctl *</span>
                        </div>
                        <button
                            x-on:click="
                                window.navigator.clipboard.writeText($refs.cmd.innerText);
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            title="Copy to clipboard"
                        >
                            <span x-show="!copied"><x-heroicon-m-clipboard class="w-5 h-5" /></span>
                            <span x-show="copied" x-cloak><x-heroicon-m-check class="w-5 h-5 text-emerald-500" /></span>
                        </button>
                    </div>
                </li>
            </ol>

            <p class="text-sm italic text-gray-500">
                <strong>Tip:</strong> You can restrict this more tightly by specifying exact commands (e.g.,
                <code>supervisorctl reread</code>, <code>supervisorctl update</code>) instead of <code>*</code>.
            </p>

        </x-filament::section>

    </div>
</x-filament-panels::page>