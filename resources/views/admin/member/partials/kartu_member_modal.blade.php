{{-- Modal Kartu Member --}}
<div class="modal fade" id="kartuMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title"><i class="fas fa-id-card"></i> Kartu Member Perpustakaan Boyolali</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Container Kartu -->
                <div id="kartuCapture">
                    <div class="kartu-container">
                        <div class="kartu-wrapper" id="kartuWrapper">
                            <!-- SISI DEPAN -->
                            <div class="kartu-depan">
                                <div class="header-kartu">
                                    <h5>KARTU ANGGOTA PERPUSTAKAAN</h5>
                                    <h6>Kabupaten Boyolali</h6>
                                </div>

                                <div class="body-kartu">
                                    <div class="foto-section">
                                        <img src="https://ui-avatars.com/api/?name=Member&size=120"
                                             class="foto-member"
                                             id="fotoMember"
                                             alt="Foto Member">
                                    </div>

                                    <div class="info-section">
                                        <div class="info-row">
                                            <strong>Nama:</strong>
                                            <span id="namaMember">-</span>
                                        </div>
                                        <div class="info-row">
                                            <strong>Email:</strong>
                                            <span id="emailMember">-</span>
                                        </div>
                                        <div class="info-row">
                                            <strong>No. HP:</strong>
                                            <span id="noHpMember">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Barcode Section - TANPA NOMOR DI BARCODE -->
                                <!-- Barcode Only – Pas & Rapi -->
                                <div class="barcode-section">
                                    <div class="barcode-wrapper">
                                        <canvas id="barcodeCanvas"></canvas>
                                    </div>
                                    <div class="nomor-barcode" id="nomorMemberBottom">-</div>
                                </div>
                            </div>

                            <!-- SISI BELAKANG -->
                            <div class="kartu-belakang">
                                <h5>⚠️ Tata Tertib Peminjaman Buku</h5>
                                <div class="tata-tertib">
                                    <ol>
                                        <li><strong>Maksimal peminjaman:</strong> 2 (dua) buku per anggota</li>
                                        <li><strong>Masa peminjaman:</strong> 7 (tujuh) hari kerja</li>
                                        <li><strong>Perpanjangan:</strong> Maksimal 1 kali perpanjangan untuk waktu 7 hari</li>
                                        <li><strong>Denda keterlambatan:</strong> Sesuai ketentuan yang berlaku</li>
                                        <li><strong>Kartu member</strong> harus dibawa setiap berkunjung ke perpustakaan</li>
                                        <li><strong>Dilarang</strong> meminjamkan kartu ini kepada orang lain</li>
                                        <li><strong>Kehilangan kartu</strong> segera laporkan ke petugas</li>
                                        <li><strong>Jaga kebersihan</strong> dan kelestarian koleksi perpustakaan</li>
                                    </ol>
                                </div>
                                <div class="footer-belakang">
                                    <p>© 2025 Perpustakaan Boyolali</p>
                                    <p>Jaga kartu ini dengan baik</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontrol -->
                <div class="controls text-center mt-4">
                    <button class="btn btn-primary btn-lg" onclick="flipCard()">
                        <i class="fas fa-sync-alt"></i> Balik Kartu
                    </button>
                    <button class="btn btn-success btn-lg" onclick="downloadCard()">
                        <i class="fas fa-download"></i> Unduh Kartu
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Container Kartu */
    .kartu-container {
        perspective: 1000px;
        width: 550px;
        height: 340px;
        margin: 20px auto;
    }

    .kartu-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.6s;
        transform-style: preserve-3d;
    }

    .kartu-wrapper.flipped {
        transform: rotateY(180deg);
    }

    .kartu-depan, .kartu-belakang {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        overflow: hidden;
    }

    .kartu-belakang {
        transform: rotateY(180deg);
    }

    /* === DEPAN === */
    .kartu-depan {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        display: flex;
        flex-direction: column;
    }

    .header-kartu {
        text-align: center;
        border-bottom: 2px solid rgba(255,255,255,0.3);
        padding-bottom: 12px;
        margin-bottom: 18px;
    }

    .header-kartu h5 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .header-kartu h6 {
        font-size: 13px;
        margin: 5px 0 0 0;
        font-weight: 400;
    }

    .body-kartu {
        display: flex;
        gap: 20px;
        margin-bottom: 8px;
    }

    .foto-section {
        flex-shrink: 0;
    }

    .foto-member {
        width: 95px;
        height: 120px;
        border-radius: 10px;
        border: 3px solid white;
        object-fit: cover;
        background: white;
    }

    .info-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .info-row {
        font-size: 12px;
        line-height: 1.5;
    }

    .info-row strong {
        display: block;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .info-row span {
        display: block;
        font-weight: 400;
    }


    .barcode-section {
        padding: 12px 18px 16px;
        border-radius: 16px;
        text-align: center;
        margin-left: 5px;
        margin-right: 5px;
        margin-bottom: 0;

        /* INI DIA YANG KAMU CARI-CARI, SAKNO! */
        /* Naik-turunin sesuka hati, pake angka NEGATIF buat naik tinggi */
        margin-top: -20px;   /* <--- UBAH ANGKA INI AJA BRO */
        /* contoh: -50px = naik lebih tinggi lagi */
        /* -30px = agak turun sedikit */
        /* 0px = normal */
        /* 20px = turun lagi */
    }

    .barcode-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .barcode-wrapper canvas {
        max-width: 100%;
        height: auto;
        display: block;
    }

    .nomor-barcode {
        font-family: 'OCR A Std', 'Courier New', monospace;
        font-size: 12px;
        font-weight: 700;
        color: #ffffff;
        margin: 8px 0 0 0;
        letter-spacing: 5px;
        text-shadow: 0 2px 6px rgba(0,0,0,0.6);
    }


    /* === BELAKANG === */
    .kartu-belakang {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 25px;
        color: #333;
        display: flex;
        flex-direction: column;
    }

    .kartu-belakang h5 {
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 18px;
        color: #667eea;
        text-transform: uppercase;
    }

    .tata-tertib {
        font-size: 11px;
        line-height: 1.6;
        flex: 1;
    }

    .tata-tertib ol {
        padding-left: 22px;
        margin: 0;
    }

    .tata-tertib li {
        margin-bottom: 8px;
    }

    .footer-belakang {
        text-align: center;
        font-size: 9px;
        color: #666;
        border-top: 1px solid #ddd;
        padding-top: 12px;
        margin-top: 12px;
    }

    .footer-belakang p {
        margin: 3px 0;
    }

    /* Button Styles */
    .controls .btn {
        margin: 0 5px;
        min-width: 160px;
    }
</style>

<script>
    function flipCard() {
        document.getElementById('kartuWrapper').classList.toggle('flipped');
    }

    function loadMemberData(data) {
        document.getElementById('namaMember').textContent = data.nama || '-';
        document.getElementById('emailMember').textContent = data.email || '-';
        document.getElementById('noHpMember').textContent = data.no_hp || '-';
        document.getElementById('nomorMemberBottom').textContent = data.nomor_member || '-';

        if (data.foto) {
            document.getElementById('fotoMember').src = data.foto;
        }

        // Generate barcode TANPA displayValue (nomor akan tampil terpisah di bawah)
        // Generate barcode — FIXED VERSION
        if (data.nomor_member && data.nomor_member !== 'BELUM ADA') {
            const canvas = document.getElementById('barcodeCanvas');

            // Ukuran lebih lebar & tinggi biar ga kepotong + nomor keliatan jelas
            const displayWidth = 360;   // naikin dari 420 → 460
            const displayHeight = 70;   // naikin dari 72 → 90

            const ratio = window.devicePixelRatio || 1;

            canvas.width = displayWidth * ratio;
            canvas.height = displayHeight * ratio;

            // CSS size ikutin JS
            canvas.style.width = displayWidth + 'px';
            canvas.style.height = displayHeight + 'px';

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            JsBarcode(canvas, data.nomor_member, {
                format: "CODE128",
                width: 2,              // naikin dari 1.6 → 2 biar garis tebel & ga kepotong
                height: displayHeight * ratio - 20,  // dikit ruang buat nomor bawah
                displayValue: false,
                margin: 10,
                background: "transparent",
                lineColor: "#ffffff",
                flat: true
            });
        }
    }

    async function downloadCard() {
        const wrapper = document.getElementById('kartuWrapper');
        const namaMember = document.getElementById('namaMember').textContent;

        // Screenshot depan
        wrapper.classList.remove('flipped');
        await new Promise(resolve => setTimeout(resolve, 300));

        html2canvas(document.querySelector('.kartu-depan'), {
            scale: 3,
            backgroundColor: null,
            logging: false,
            useCORS: true
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = `Kartu_Member_${namaMember}_Depan.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        });

        // Screenshot belakang
        await new Promise(resolve => setTimeout(resolve, 700));
        wrapper.classList.add('flipped');
        await new Promise(resolve => setTimeout(resolve, 800));

        html2canvas(document.querySelector('.kartu-belakang'), {
            scale: 3,
            backgroundColor: null,
            logging: false
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = `Kartu_Member_${namaMember}_Belakang.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        });

        // Reset ke depan
        await new Promise(resolve => setTimeout(resolve, 700));
        wrapper.classList.remove('flipped');
    }
</script>
