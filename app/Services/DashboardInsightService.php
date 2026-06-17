<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardInsightService
{
    public function generate(array $payload)
    {
        $fallback = $this->buildFallbackInsight($payload);
        $apiKey = (string) config('services.gemini.api_key');
        $model = (string) config('services.gemini.model', 'gemini-2.5-flash');
        $baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $url = $baseUrl . '/models/' . $model . ':generateContent';

        if ($apiKey === '') {
            return $this->fallbackResult($fallback, 'GEMINI_API_KEY belum diisi di file .env.');
        }

        try {
            $response = $this->sendGeminiRequest($url, $apiKey, $this->buildGeminiPayload($payload));

            if ($response->successful()) {
                $text = $this->extractGeminiText($response->json());

                if ($text !== '') {
                    $normalizedText = $this->normalizeInsight($text);

                    if ($this->needsExpansion($normalizedText)) {
                        $expandedResponse = $this->sendGeminiRequest($url, $apiKey, $this->buildExpansionPayload($payload, $normalizedText));

                        if ($expandedResponse->successful()) {
                            $expandedText = $this->extractGeminiText($expandedResponse->json());

                            if ($expandedText !== '') {
                                $normalizedText = $this->normalizeInsight($expandedText);

                                if ($this->needsExpansion($normalizedText)) {
                                    $secondExpandedResponse = $this->sendGeminiRequest($url, $apiKey, $this->buildExpansionPayload($payload, $normalizedText, true));

                                    if ($secondExpandedResponse->successful()) {
                                        $secondExpandedText = $this->extractGeminiText($secondExpandedResponse->json());

                                        if ($secondExpandedText !== '') {
                                            $normalizedText = $this->normalizeInsight($secondExpandedText);
                                        }
                                    }
                                }
                            }
                        } else {
                            Log::warning('Gemini expansion request gagal', [
                                'status' => $expandedResponse->status(),
                                'body' => $expandedResponse->body(),
                            ]);
                        }
                    }

                    return [
                        'insight' => $normalizedText,
                        'source' => 'gemini',
                        'used_fallback' => false,
                        'error_message' => null,
                    ];
                }

                Log::warning('Gemini response kosong', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackResult($fallback, 'Gemini merespons, tetapi isi penjelasan kosong.');
            }

            Log::warning('Gemini request gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->fallbackResult(
                $fallback,
                'Gemini mengembalikan HTTP ' . $response->status() . '. ' . $this->shortenMessage($response->body())
            );
        } catch (ConnectionException $exception) {
            Log::error('Gemini connection error', [
                'message' => $exception->getMessage(),
            ]);

            return $this->fallbackResult($fallback, 'Koneksi dari PHP ke Gemini gagal. ' . $this->shortenMessage($exception->getMessage()));
        } catch (\Throwable $exception) {
            Log::error('Gemini unexpected error', [
                'message' => $exception->getMessage(),
            ]);

            return $this->fallbackResult($fallback, $this->shortenMessage($exception->getMessage()));
        }
    }

    protected function sendGeminiRequest($url, $apiKey, array $payload)
    {
        return Http::timeout((int) config('services.gemini.timeout', 30))
            ->acceptJson()
            ->post($url . '?key=' . urlencode($apiKey), $payload);
    }

    protected function buildGeminiPayload(array $payload)
    {
        return [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $this->buildPrompt($payload),
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.5,
                'topP' => 0.9,
                'maxOutputTokens' => 1400,
            ],
        ];
    }

    protected function buildExpansionPayload(array $payload, $initialText, $isRetry = false)
    {
        return [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $this->buildExpansionPrompt($payload, $initialText, $isRetry),
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.5,
                'topP' => 0.9,
                'maxOutputTokens' => 1800,
            ],
        ];
    }

    protected function buildPrompt(array $payload)
    {
        $title = $payload['title'] ?? 'Chart dashboard';
        $description = $payload['description'] ?? '';
        $scope = $payload['scope_label'] ?? 'Jawa Timur';
        $filters = $payload['filters'] ?? [];
        $labels = array_values($payload['labels'] ?? []);
        $datasets = $payload['datasets'] ?? [];
        $rows = $payload['rows'] ?? [];

        $datasetSummary = array_map(function ($dataset) {
            return [
                'label' => $dataset['label'] ?? 'Dataset',
                'data' => array_values($dataset['data'] ?? []),
                'format' => $dataset['format'] ?? 'number',
            ];
        }, array_slice($datasets, 0, 8));

        $rowSummary = array_slice($rows, 0, 40);

        $promptData = [
            'title' => $title,
            'description' => $description,
            'scope' => $scope,
            'filters' => $filters,
            'labels' => array_slice($labels, 0, 40),
            'datasets' => $datasetSummary,
            'rows' => $rowSummary,
        ];

        return implode("\n", [
            'Anda adalah analis senior dashboard PAD pemerintah daerah.',
            'Tugas Anda adalah membaca data chart atau tabel yang diberikan, lalu menulis penjelasan otomatis dalam Bahasa Indonesia yang formal, ringkas, tajam, dan rapi.',
            'Penting: analisis harus ditentukan langsung dari data yang diberikan. Jangan menunggu arahan pengguna tentang tren, nilai tertinggi, nilai terendah, anomali, pola perubahan, kesenjangan, konsistensi, pergeseran kontribusi, atau perbandingan. Jika hal-hal itu terlihat dari data, jelaskan sendiri secara aktif.',
            'Jangan mengarang data yang tidak ada. Jangan menyebut bahwa Anda adalah AI.',
            'Tulis dalam 2 sampai 3 paragraf, bukan poin-poin.',
            'Panjang total harus sekitar 220 sampai 380 kata.',
            'Paragraf pertama menjelaskan gambaran umum chart dan temuan utama yang paling terlihat dari data.',
            'Paragraf kedua harus memperdalam pembacaan data, termasuk dominasi kategori, selisih, pola distribusi, atau hubungan antar nilai yang paling menonjol.',
            'Paragraf ketiga, jika diperlukan, menjelaskan implikasi singkat atau tindak lanjut yang relevan berdasarkan data.',
            'Jangan terlalu pendek. Pastikan ada konteks, temuan utama, pembacaan data yang cukup, dan implikasi singkat.',
            'Jika chart berupa tabel atau non-tren, tetap berikan penjelasan ringkas berdasarkan komposisi, urutan nilai, selisih, proporsi, dan hubungan antar kolom yang terlihat.',
            'Data chart/tabel dalam JSON:',
            json_encode($promptData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    protected function buildExpansionPrompt(array $payload, $initialText, $isRetry = false)
    {
        $retryInstruction = $isRetry
            ? 'Versi sebelumnya masih terlalu pendek. Kali ini pastikan hasil benar-benar berada di kisaran 100 sampai 200 kata.'
            : 'Perluas analisis berikut sedikit agar lebih utuh, tetapi tetap ringkas.';

        return implode("\n", [
            $retryInstruction,
            'Jangan mengubah fakta dasar dari data. Jangan menambahkan angka yang tidak tersedia.',
            'Hasil akhir harus tetap berupa 2 sampai 3 paragraf dengan panjang total sekitar 220 sampai 380 kata.',
            'Tambahkan pembacaan yang lebih jelas tentang pola data, perbandingan antar elemen, interpretasi kinerja, dan implikasi kebijakan, tetapi tetap formal dan natural.',
            'Jangan terlalu pendek. Pastikan hasil terasa utuh, tetapi tidak bertele-tele.',
            'Jangan awali jawaban dengan frasa seperti "Berikut adalah", "Analisis yang diperluas", atau kalimat pengantar serupa. Langsung tulis isi analisisnya.',
            'Versi awal:',
            $initialText,
            'Data sumber:',
            json_encode([
                'title' => $payload['title'] ?? '',
                'description' => $payload['description'] ?? '',
                'scope' => $payload['scope_label'] ?? '',
                'filters' => $payload['filters'] ?? [],
                'labels' => array_slice(array_values($payload['labels'] ?? []), 0, 40),
                'datasets' => array_slice($payload['datasets'] ?? [], 0, 8),
                'rows' => array_slice($payload['rows'] ?? [], 0, 40),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    protected function extractGeminiText(array $response)
    {
        $candidates = $response['candidates'] ?? [];

        foreach ($candidates as $candidate) {
            $parts = $candidate['content']['parts'] ?? [];

            foreach ($parts as $part) {
                $text = trim((string) ($part['text'] ?? ''));

                if ($text !== '') {
                    return $text;
                }
            }
        }

        return '';
    }

    protected function buildFallbackInsight(array $payload)
    {
        $title = $payload['title'] ?? 'Visual dashboard';
        $scope = $payload['scope_label'] ?? 'wilayah aktif';
        $rows = $payload['rows'] ?? [];
        $datasets = $payload['datasets'] ?? [];
        $labels = $payload['labels'] ?? [];

        if (!empty($rows)) {
            $rowCount = count($rows);
            $firstRow = $rows[0];
            $firstKeys = array_keys($firstRow);
            $sampleKey = $firstKeys[0] ?? 'kategori';
            $sampleValue = $firstRow[$sampleKey] ?? '-';

            return trim($title . ' pada ' . $scope . ' menampilkan ' . $rowCount
                . ' baris data utama. Sampel teratas berada pada ' . $sampleKey . ' "'
                . $sampleValue . '" sehingga visual ini dapat dipakai untuk membaca distribusi nilai, membandingkan capaian antarkategori, serta melihat keterkaitan antar indikator yang tersedia pada filter aktif. Secara umum, struktur data ini berguna untuk menilai posisi relatif tiap kategori dan mengidentifikasi area yang memerlukan perhatian lanjutan. Penjelasan ini dipakai sebagai cadangan ketika koneksi ke Gemini belum tersedia.');
        }

        if (!empty($datasets)) {
            $fragments = [];

            foreach ($datasets as $dataset) {
                $numbers = array_map('floatval', $dataset['data'] ?? []);

                if (empty($numbers)) {
                    continue;
                }

                $maxValue = max($numbers);
                $minValue = min($numbers);
                $maxIndex = array_search($maxValue, $numbers, true);
                $minIndex = array_search($minValue, $numbers, true);
                $maxLabel = $labels[$maxIndex] ?? 'kategori tertinggi';
                $minLabel = $labels[$minIndex] ?? 'kategori terendah';

                $fragments[] = ($dataset['label'] ?? 'Nilai') . ' tertinggi terlihat pada ' . $maxLabel
                    . ', sedangkan nilai terendah berada pada ' . $minLabel . '.';
            }

            return trim($title . ' pada ' . $scope . ' memperlihatkan pola perbandingan antar kategori yang dapat digunakan untuk membaca arah capaian dan distribusi kontribusi pada filter aktif. '
                . implode(' ', $fragments) . ' Secara umum, chart ini membantu menilai kategori yang lebih dominan, melihat rentang jarak antar nilai, dan mengenali bagian yang berpotensi memerlukan evaluasi atau penguatan kebijakan. Ringkasan ini dipakai sebagai fallback saat respons Gemini belum tersedia.');
        }

        return trim($title . ' pada ' . $scope . ' dapat digunakan sebagai ringkasan visual untuk membaca kondisi PAD pada filter aktif. Visual ini membantu melihat hubungan dasar antar angka yang ditampilkan sekaligus memberi arah awal untuk analisis lebih lanjut dan penentuan tindak lanjut singkat. Penjelasan otomatis Gemini sedang tidak tersedia, sehingga dokumen ini memakai ringkasan cadangan berbasis data yang tampil di dashboard.');
    }

    protected function normalizeInsight($text)
    {
        $text = preg_replace("/\r\n|\r/", "\n", (string) $text);
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        $text = preg_replace('/^(berikut adalah\s+analisis(?:\s+yang\s+diperluas)?\s*:\s*)/i', '', $text);
        $text = preg_replace('/^(analisis(?:\s+yang\s+diperluas)?\s*:\s*)/i', '', $text);

        return trim($text);
    }

    protected function fallbackResult($insight, $message)
    {
        return [
            'insight' => $insight,
            'source' => 'fallback',
            'used_fallback' => true,
            'error_message' => $message,
        ];
    }

    protected function shortenMessage($message)
    {
        $text = trim(preg_replace('/\s+/', ' ', (string) $message));

        if (strlen($text) > 320) {
            return substr($text, 0, 317) . '...';
        }

        return $text;
    }

    protected function needsExpansion($text)
    {
        preg_match_all('/\p{L}+/u', strip_tags((string) $text), $matches);
        $wordCount = count($matches[0]);
        return $wordCount < 180;
    }
}
