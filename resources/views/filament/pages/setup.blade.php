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
                🔐 Secure Copy Setup (Required for Production)
            </x-slot>

            <div
                class="mb-6 p-4 rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800">
                <div class="flex items-start gap-3">
                    <x-heroicon-m-information-circle class="w-6 h-6 shrink-0 text-primary-600 dark:text-primary-400" />
                    <div class="text-sm">
                        <strong>Production-Grade Architecture:</strong> Laravel writes configs to your project
                        directory,
                        then calls a secure sudo script to copy files to the system supervisor directory.
                        This ensures Laravel never needs direct write access to system directories.
                    </div>
                </div>
            </div>

            @php
                $systemUser = 'www-data'; // default
                if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
                    $systemUser = posix_getpwuid(posix_geteuid())['name'] ?? 'www-data';
                }
                $supervisorPath = config('supervisor-manager.conf_path', '/opt/homebrew/etc/supervisor.d');
            @endphp

            <div class="mb-4 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <div class="text-sm font-semibold mb-2">Detected Configuration:</div>
                <div class="text-xs font-mono space-y-1">
                    <div><span class="text-gray-500">System User:</span> <span
                            class="text-primary-600 dark:text-primary-400">{{ $systemUser }}</span></div>
                    <div><span class="text-gray-500">Supervisor Path:</span> <span
                            class="text-primary-600 dark:text-primary-400">{{ $supervisorPath }}</span></div>
                </div>
            </div>

            <h3 class="font-bold text-lg mb-3 flex items-center gap-2">
                <x-heroicon-m-bolt class="w-5 h-5 text-primary-600" />
                Quick Setup (Recommended)
            </h3>

            <p class="mb-4 text-sm">Run the automated setup script:</p>

            <div x-data="{ copied: false }" class="relative group mb-6">
                <div
                    class="bg-gray-900 dark:bg-gray-950 p-4 rounded-lg font-mono text-xs text-gray-300 pr-10 space-y-1">
                    <div x-ref="cmd1">cd vendor/iperamuna/laravel-supervisor-manager/scripts</div>
                    <div>bash setup-secure-copy.sh</div>
                </div>
                <button x-on:click="
                        window.navigator.clipboard.writeText($refs.cmd1.innerText + '\nbash setup-secure-copy.sh');
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    " class="absolute top-3 right-3 text-gray-400 hover:text-gray-200 transition-colors"
                    title="Copy to clipboard">
                    <span x-show="!copied"><x-heroicon-m-clipboard class="w-5 h-5" /></span>
                    <span x-show="copied" x-cloak><x-heroicon-m-check class="w-5 h-5 text-emerald-500" /></span>
                </button>
            </div>

            <h3 class="font-bold text-lg mb-3 flex items-center gap-2">
                <x-heroicon-m-wrench-screwdriver class="w-5 h-5 text-gray-600" />
                Manual Setup
            </h3>

            <ol class="list-decimal list-inside space-y-4 mb-4">
                <li>
                    <strong>Install the copy script:</strong>
                    <div x-data="{ copied: false }" class="relative group mt-2">
                        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded font-mono text-xs pr-10 space-y-1">
                            <div x-ref="cmd2">sudo cp vendor/iperamuna/laravel-supervisor-manager/scripts/supervisor-copy
                                /usr/local/bin/</div>
                            <div>sudo chmod +x /usr/local/bin/supervisor-copy</div>
                        </div>
                        <button x-on:click="
                                window.navigator.clipboard.writeText($refs.cmd2.innerText + '\nsudo chmod +x /usr/local/bin/supervisor-copy');
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            title="Copy to clipboard">
                            <span x-show="!copied"><x-heroicon-m-clipboard class="w-5 h-5" /></span>
                            <span x-show="copied" x-cloak><x-heroicon-m-check class="w-5 h-5 text-emerald-500" /></span>
                        </button>
                    </div>
                </li>

                <li>
                    <strong>Configure sudoers:</strong>
                    <div x-data="{ copied: false }" class="relative group mt-2 mb-2">
                        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded font-mono text-xs pr-10">
                            <span x-ref="cmd3">sudo visudo</span>
                        </div>
                        <button x-on:click="
                                window.navigator.clipboard.writeText($refs.cmd3.innerText);
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            title="Copy to clipboard">
                            <span x-show="!copied"><x-heroicon-m-clipboard class="w-5 h-5" /></span>
                            <span x-show="copied" x-cloak><x-heroicon-m-check class="w-5 h-5 text-emerald-500" /></span>
                        </button>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 my-2">Then add this line:</p>

                    <div x-data="{ copied: false }" class="relative group mt-2">
                        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded font-mono text-xs pr-10">
                            <span x-ref="cmd4">{{ $systemUser }} ALL=(root) NOPASSWD: /usr/local/bin/supervisor-copy
                                *</span>
                        </div>
                        <button x-on:click="
                                window.navigator.clipboard.writeText($refs.cmd4.innerText);
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            title="Copy to clipboard">
                            <span x-show="!copied"><x-heroicon-m-clipboard class="w-5 h-5" /></span>
                            <span x-show="copied" x-cloak><x-heroicon-m-check class="w-5 h-5 text-emerald-500" /></span>
                        </button>
                    </div>
                </li>

                <li>
                    <strong>Update your .env file:</strong>
                    <div x-data="{ copied: false }" class="relative group mt-2">
                        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded font-mono text-xs pr-10 space-y-1">
                            <div x-ref="cmd5">SUPERVISOR_SYSTEM_USER={{ $systemUser }}</div>
                            <div>SUPERVISOR_CONF_PATH={{ $supervisorPath }}</div>
                            <div>SUPERVISOR_USE_SECURE_COPY=true</div>
                        </div>
                        <button x-on:click="
                                window.navigator.clipboard.writeText('SUPERVISOR_SYSTEM_USER={{ $systemUser }}\nSUPERVISOR_CONF_PATH={{ $supervisorPath }}\nSUPERVISOR_USE_SECURE_COPY=true');
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            title="Copy to clipboard">
                            <span x-show="!copied"><x-heroicon-m-clipboard class="w-5 h-5" /></span>
                            <span x-show="copied" x-cloak><x-heroicon-m-check class="w-5 h-5 text-emerald-500" /></span>
                        </button>
                    </div>
                </li>
            </ol>

            <div class="mt-4 p-4 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                <h4 class="font-semibold text-sm mb-2 flex items-center gap-2">
                    <x-heroicon-m-light-bulb class="w-5 h-5 text-yellow-500" />
                    Why This Approach?
                </h4>
                <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                    <li>✅ Laravel never needs write access to system directories</li>
                    <li>✅ Controlled sudo access to a single, auditable script</li>
                    <li>✅ Automatic supervisorctl reread/update after deployment</li>
                    <li>✅ Works on macOS (Homebrew) and Linux systems</li>
                </ul>
            </div>

        </x-filament::section>


    </div>
</x-filament-panels::page>
