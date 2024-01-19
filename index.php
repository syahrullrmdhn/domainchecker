<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/styles.css">
    <title>Domain Checker</title>
</head>
<body class="bg-aqua-100 p-8">

    <div class="max-w-md mx-auto bg-white p-6 rounded-md shadow-md">
        <h1 class="text-2xl font-bold mb-4">Domain Checker</h1>

        <div x-data="domainChecker()">
            <input x-model="domain" type="text" class="border rounded-md px-4 py-2 w-full mb-4" placeholder="Masukkan domain...">
            
            <button @click="checkDomain" class="bg-blue-500 text-white py-2 px-4 rounded-md">Check Domain</button>

            <div x-show="status === 'online'" class="mt-4">
                <span class="text-green-500">Domain Online</span>
                <button @click="openModal" class="ml-2 underline cursor-pointer">Lihat Informasi WHOIS</button>
            </div>

            <div x-show="status === 'offline'" class="mt-4">
                <span class="text-red-500">Domain Offline</span>
            </div>

            <!-- Modal -->
            <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-800 bg-opacity-50">
                <div class="bg-white p-8 rounded-md shadow-md max-w-3xl w-full">
                    <div class="flex justify-end">
                        <button @click="closeModal" class="text-gray-700 hover:text-gray-900">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <h2 class="text-xl font-bold mb-4">Informasi WHOIS</h2>
                    <pre x-html="formatWhoisInfo(whoisInfo)" class="whitespace-pre-line"></pre>

                    <!-- Tombol Tutup dan Download -->
                    <div class="mt-6 flex justify-end">
                        <button @click="closeModal" class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md mr-2">Tutup</button>
                        <a :href="downloadUrl" download="whois_info.txt" class="bg-blue-500 text-white py-2 px-4 rounded-md cursor-pointer">Unduh</a>
                    </div>
                    <!-- End Tombol Tutup dan Download -->
                </div>
            </div>
            <!-- End Modal -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <script>
        function domainChecker() {
            return {
                domain: '',
                status: '',
                whoisInfo: {},
                isModalOpen: false,
                checkDomain() {
                    let domain = this.domain.trim();
                    if (domain === '') {
                        alert('Mohon masukkan domain terlebih dahulu.');
                        return;
                    }

                    this.status = ''; // Reset status

                    // Get WHOIS information from the API
                    fetch(`ping.php?domain=${encodeURIComponent(domain)}`)
                        .then(response => response.json())
                        .then(data => {
                            this.status = data.online ? 'online' : 'offline';
                            
                            if (data.whois) {
                                this.whoisInfo = data.whois;
                            } else {
                                this.whoisInfo = { error: 'Informasi WHOIS tidak tersedia.' };
                            }
                        });
                },
                openModal() {
                    this.isModalOpen = true;
                },
                closeModal() {
                    this.isModalOpen = false;
                },
                formatWhoisInfo(whoisInfo) {
                    // Format the WHOIS information for better display
                    if (!whoisInfo || whoisInfo.error) {
                        return whoisInfo.error || 'Informasi WHOIS tidak tersedia.';
                    }

                    let formattedInfo = '<ul>';
                    for (let key in whoisInfo) {
                        if (typeof whoisInfo[key] === 'object') {
                            formattedInfo += `<li><strong>${key}:</strong> ${this.formatNestedObject(whoisInfo[key])}</li>`;
                        } else {
                            formattedInfo += `<li><strong>${key}:</strong> ${whoisInfo[key]}</li>`;
                        }
                    }
                    formattedInfo += '</ul>';

                    return formattedInfo;
                },
                formatNestedObject(nestedObject) {
                    // Format nested objects
                    let nestedFormattedInfo = '<ul>';
                    for (let nestedKey in nestedObject) {
                        if (typeof nestedObject[nestedKey] === 'object') {
                            nestedFormattedInfo += `<li><strong>${nestedKey}:</strong> ${this.formatNestedObject(nestedObject[nestedKey])}</li>`;
                        } else {
                            nestedFormattedInfo += `<li><strong>${nestedKey}:</strong> ${nestedObject[nestedKey]}</li>`;
                        }
                    }
                    nestedFormattedInfo += '</ul>';

                    return nestedFormattedInfo;
                },
                get downloadUrl() {
                    // Generate a data URL for downloading the WHOIS info as a text file
                    const whoisText = this.formatWhoisInfo(this.whoisInfo);
                    const blob = new Blob([whoisText], { type: 'text/plain' });
                    return URL.createObjectURL(blob);
                },
            };
        }
    </script>
</body>
</html>
