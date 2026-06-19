import { EdgeTTS } from 'edge-tts-universal';
import { mkdir, writeFile, access } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

const root = process.cwd();
const voice = process.env.QUEUE_TTS_VOICE || 'id-ID-GadisNeural';
const force = process.argv.includes('--force');

const assets = [
    ['public/audio/queue/phrases/nomor-antrian.mp3', 'Nomor antrian.'],
    ['public/audio/queue/phrases/silakan-menuju.mp3', 'Silakan menuju.'],
    ['public/audio/queue/phrases/layanan.mp3', 'Layanan.'],
    ['storage/app/public/announcement/services/verifikasi-berkas.mp3', 'Verifikasi Berkas.'],
    ['storage/app/public/announcement/services/wawancara.mp3', 'Wawancara.'],
    ['storage/app/public/announcement/counters/loket-verifikasi-1.mp3', 'Loket Verifikasi Satu.'],
    ['storage/app/public/announcement/counters/loket-verifikasi-2.mp3', 'Loket Verifikasi Dua.'],
    ['storage/app/public/announcement/counters/loket-wawancara-1.mp3', 'Loket Wawancara Satu.'],
    ['storage/app/public/announcement/counters/loket-wawancara-2.mp3', 'Loket Wawancara Dua.'],
];

const digits = {
    0: 'Nol.',
    1: 'Satu.',
    2: 'Dua.',
    3: 'Tiga.',
    4: 'Empat.',
    5: 'Lima.',
    6: 'Enam.',
    7: 'Tujuh.',
    8: 'Delapan.',
    9: 'Sembilan.',
};

for (const [digit, text] of Object.entries(digits)) {
    assets.push([`public/audio/queue/digits/${digit}.mp3`, text]);
}

for (const letter of 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    assets.push([`public/audio/queue/letters/${letter.toLowerCase()}.mp3`, `${letter}.`]);
}

async function exists(file) {
    try {
        await access(file);
        return true;
    } catch {
        return false;
    }
}

async function synthesize(relativePath, text) {
    const output = path.join(root, relativePath);

    if (! force && await exists(output)) {
        console.log(`skip ${relativePath}`);
        return;
    }

    await mkdir(path.dirname(output), { recursive: true });

    const tts = new EdgeTTS(text, voice, {
        rate: '-4%',
        volume: '+0%',
        pitch: '+0Hz',
    });
    const result = await tts.synthesize();
    const audio = Buffer.from(await result.audio.arrayBuffer());

    await writeFile(output, audio);
    console.log(`created ${relativePath}`);
}

async function createBell() {
    const relativePath = 'public/audio/queue/system/bell.wav';
    const output = path.join(root, relativePath);

    if (! force && await exists(output)) {
        console.log(`skip ${relativePath}`);
        return;
    }

    const sampleRate = 44100;
    const seconds = 1.25;
    const sampleCount = Math.floor(sampleRate * seconds);
    const dataSize = sampleCount * 2;
    const wav = Buffer.alloc(44 + dataSize);

    wav.write('RIFF', 0);
    wav.writeUInt32LE(36 + dataSize, 4);
    wav.write('WAVE', 8);
    wav.write('fmt ', 12);
    wav.writeUInt32LE(16, 16);
    wav.writeUInt16LE(1, 20);
    wav.writeUInt16LE(1, 22);
    wav.writeUInt32LE(sampleRate, 24);
    wav.writeUInt32LE(sampleRate * 2, 28);
    wav.writeUInt16LE(2, 32);
    wav.writeUInt16LE(16, 34);
    wav.write('data', 36);
    wav.writeUInt32LE(dataSize, 40);

    for (let index = 0; index < sampleCount; index++) {
        const time = index / sampleRate;
        const attack = Math.min(1, time / 0.012);
        const decay = Math.exp(-3.4 * time);
        const first = Math.sin(2 * Math.PI * 1046.5 * time);
        const second = Math.sin(2 * Math.PI * 1318.5 * time) * 0.55;
        const shimmer = Math.sin(2 * Math.PI * 2093 * time) * 0.18;
        const sample = Math.max(-1, Math.min(1, (first + second + shimmer) * attack * decay * 0.38));

        wav.writeInt16LE(Math.round(sample * 32767), 44 + (index * 2));
    }

    await mkdir(path.dirname(output), { recursive: true });
    await writeFile(output, wav);
    console.log(`created ${relativePath}`);
}

await createBell();

for (const [relativePath, text] of assets) {
    await synthesize(relativePath, text);
}

console.log(`Queue audio pack ready with voice ${voice}.`);
