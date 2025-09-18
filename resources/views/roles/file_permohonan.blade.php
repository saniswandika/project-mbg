<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Terdaftar Yayasan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* margin: 20px; */
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            margin-top: 10px;
        }
        
        .content {
            margin-bottom: 20px;
        }
        .container {
            /* margin-top: 200px; */
            overflow: auto;
        }
        .column {
            width: 60%;
            float: left;
            box-sizing: border-box;
        }
        .column3 {
            width: 40%;
            float: left;
            box-sizing: border-box;
        }

        .column2 {
      
            float: left;
            box-sizing: border-box;
        }
        div.c {
            position: absolute;
            right: 90px;  
            width: 400px;
            height: 160px;
        } 
        .image-container {
            position: relative;
            margin-top: 20%;
            margin-left: 20%;
        }
        .overlay-text {
            position: absolute;
            left: 50%;
            transform: translate(-50%, -50%);
            padding-top: 7px;
            width: 400px;
            height: 200px;
            margin-left: 20%;
        }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <img src="{{ public_path('assets/img/Lambang_Kota_Bandung.png') }}"  width="130px;" alt="Gambar" /></td>
    </div>

    <div class="header">
        <span  style="font-size:24px; margin-top: 2%">PEMERINTAH KOTA BANDUNG<br style="font-weight:bold;">
            <span style="font-weight: bold; font-size:35px;">DINAS SOSIAL</span>
        </span >
        <div>
            <span  style="font-size:18px; text-align:center; margin-top:2px;">Jalan Babakan Karet (Belakang Rusunawa Rancacili) Kelurahan Derwati<br>
                Kecamatan Rancasari Kota Bandung</span>
                <hr size="4" style="color: black;" width="100%">
                <hr size="4" style="color: black; margin-top:-1%" width="100%">
            <h1  style="font-size: 20px; text-align:center; margin-top:-2px;">PENETAPAN TERDAFTAR<br>
                SEBAGAI LEMBAGA KESEJAHTERAAN SOSIAL (LKS)
            </h1>
            <div style="margin-top: -30px">
                <p style="font-size: 20px; text-align:center;">Nomor : {{ $rekomendasiTerdaftaryayasan->Nomor_Surat}}
                </p>
            </div>
            
        </div>
       
    </div>
    <div style="margin-top: -30px">
       <div style=" font-size:16px; word-spacing: 0px; text-align: justify; ">
            <p style="line-height: 1.5;">
               Berdasarkan Undang-Undang RI No. 11 tahun 2009 tentang Kesejahteraan Sosial dan peraturan
               Derah Kota Bandung Nomor: 24 Tahun 2012 tentang Penyelenggaraan dan penerangan Kesejahteraan sosial,
               Kepala Dinas Sosial Kota Bandung Menyatakan Bahwa :
            </p>
        </div>
        <div style="font-size:16px; text-align: justify;">
            <table style="font-size:15px;">
                    <tr>
                        <td style="vertical-align: top; padding-top: 0; padding-left: 6px;">Nama Lembaga</td>
                        <td style="vertical-align: top; padding-top: 0; padding-left: 50px;">:</td>
                        <td style="vertical-align: top; padding-top: 0; padding-left: 6px; font-weight: bold;">{{ $rekomendasiTerdaftaryayasan->nama_lembaga}}</td>
                    </tr>
                      <tr>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">Alamat</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 50px;">:</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">{{ $rekomendasiTerdaftaryayasan->alamat_lembaga }}</td>
                      </tr>
                      <tr>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">Akta Notaris</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 50px;">:</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px; font-weight: bold;">{{ $rekomendasiTerdaftaryayasan->nama_notaris}}<br>
                                                                                                Nomor : {{ $rekomendasiTerdaftaryayasan->notgl_akta }}</td>
                      </tr>
                      <tr>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">Nama Ketua</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 50px;">:</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px; font-weight: bold;">{{ $rekomendasiTerdaftaryayasan->nama_ketua}}</td>
                      </tr>
                      <tr>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">Jenis Penyelenggaraan Kesos</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 50px;">:</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">{{ $rekomendasiTerdaftaryayasan->jenis_kesos}}</td>
                      </tr> 
                      <tr>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">Status</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 50px;">:</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">{{ $rekomendasiTerdaftaryayasan->status}}</td>
                      </tr>                    
                      <tr>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">Lingkup Wilayah Kerja</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 50px;">:</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">{{ $rekomendasiTerdaftaryayasan->Lingkup_Wilayah_Kerja }}</td>
                      </tr> 
                      <tr>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">Tipe </td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 50px;">:</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">{{ $rekomendasiTerdaftaryayasan->tipe}}</td>
                      </tr> 
                      <tr>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px;">Masa Berlaku</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 50px;">:</td>
                        <td style="vertical-align: top; padding-top: 10px; padding-left: 6px; font-weight: bold;">{{ $rekomendasiTerdaftaryayasan->tgl_mulai}} s/d {{ $rekomendasiTerdaftaryayasan->tgl_selesai}}</td>
                      </tr> 
            </table>
        </div>
        <div style="font-size:16px; text-align: justify;">
            <p style="line-height: 1.5;">
              Telah Tedaftar Sebagai Lembaga Kesejahteraan Sosial (LKS) yang sewaktu-waktu dapat dibatalkan apabila dalam Penyelenggaraan
              Kesejahteraan sosial melanggar ketentuan peraturan perundang undangan, serta wajib melakukan daftar ulang 1 (satu) tahun sekali
              dan mengirimkan laporan pelaksanaan kegiatan setahun
            </p>
        </div>
    </div>    
    <div class="c">
        <div style="text-align: center">
            <div class="image-container">
                @if ($rekomendasiTerdaftaryayasan->validasi_surat == '1' && $rekomendasiTerdaftaryayasan->updatedby == '9')
                    <img src="{{ public_path('assets/img/tandaTangan.png') }}" style="margin-left:120px; " width="100px;" alt="Gambar" />
                @else
                {{-- <img src="{{ public_path('assets/img/tandaTangan.png') }}" style="margin-left:120px; margin-top: -2px" width="100px;" alt="Gambar" /> --}}
                @endif
                <div class="overlay-text">
                    <span  style="font-size:18px; text-align:center; margin-top:2px;">Bandung, {{ $tanggal }}</span>
                    <h1  style="font-size: 18px; text-align:center; margin-top:-2px; margin-bottom:100px;"> KEPALA DINAS SOSIAL <br> KOTA BANDUNG
                    </h1>
                    <span  style="font-size: 18px; text-align:center; margin-top:-20px; font-weight: bold; margin-top:8%">H.SONI BACKHTIYAR, S.Sos., M.Si<br>
                        {{-- Sebagai Lembaga Kesejahteraan Sosial(LKS) --}}
                    </span>
                    <span  style="font-size:18px; text-align:center;"> Pembina Tingkat I</span>
                    <h1  style=" font-weight: normal; font-size: 18px; text-align:center; margin-top:-3px; ">
                        NIP. 19750625 199403 1 001
                    </h1>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
