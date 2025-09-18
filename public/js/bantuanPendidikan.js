
        //                 // Data dari PHP blade yang diambil dari query
        //                 var dataPerBulanBp = <?php echo json_encode($dataPerBulanBantuanPendidikan); ?>;

        //                 // Ambil elemen canvas untuk grafik
        //                 var ctxBp = document.getElementById('histogramChart').getContext('2d');

        //                 // Ubah data menjadi bentuk yang dapat digunakan oleh Chart.js
        //                 var labelsBp = Object.keys(dataPerBulanBp);
        //                 var dataBp = Object.values(dataPerBulanBp);

        //                 // Tetapkan palet warna yang sesuai dengan data yang muncul pada grafik
        //                 var bulanColorsBp = {
        //                     <?php
        //                     $colors = [
        //                         'rgba(255, 99, 132, 0.5)',
        //                         'rgba(54, 162, 235, 0.5)',
        //                         'rgba(255, 206, 86, 0.5)',
        //                         'rgba(75, 192, 192, 0.5)',
        //                         'rgba(153, 102, 255, 0.5)',
        //                         'rgba(255, 159, 64, 0.5)',
        //                         'rgba(50, 205, 50, 0.5)',
        //                         'rgba(255, 69, 0, 0.5)',
        //                         'rgba(139, 69, 19, 0.5)',
        //                         'rgba(238, 130, 238, 0.5)'
        //                     ];
        //                     foreach ($dataPerBulan as $bulan => $value) {
        //                         echo "'$bulan': '" . array_shift($colors) . "',";
        //                     }
        //                     ?>
        //                 };

        //                 // Konfigurasi untuk grafik histogram
        //                 var configBp = {
        //                     type: 'bar',
        //                     data: {
        //                         labels: labelsbp,
        //                         datasets: [{
        //                             label: 'Jumlah Data per Bulan 2023', // Sesuaikan label yang diinginkan
        //                             data: dataBp,
        //                             backgroundColor: labels.map(bulan => bulanColorsBp[bulan]),
        //                             borderColor: 'rgba(54, 162, 235, 1)',
        //                             borderWidth: 1
        //                         }]
        //                     },
        //                     options: {
        //                         scales: {
        //                             x: {
        //                                 type: 'category',
        //                                 offset: true
        //                             },
        //                             y: {
        //                                 beginAtZero: true
        //                             }
        //                         }
        //                     }
        //                 };
        // var myChartBp = new Chart(ctxBp, configBp);