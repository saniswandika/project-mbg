import geojsonData from './geojsonKotaBandung.json' assert {type: 'json'};
MicroModal.init();

var mapOptions = {
   center: [-6.9034495, 107.6431575],
   zoom: 13
}
// option dari laflet untuk inisiasi pertama kali

// tahapan inisiasi getCountKelurahandtks
var map = new L.map('map', mapOptions);// ini classs dari leaflet untuk inisialisasi map

var layer = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'); //setelah itu buat layer bisa untuk menampilkan nama, maker inline outline

map.addLayer(layer); // 



let bandungVillages = [];
var data = JSON.parse(JSON.stringify(geojsonData));

for (let i = 0; i < data.length; i++) {
   if (data[i].district === "KOTA BANDUNG") {
      bandungVillages.push(data[i])
   }
}



// Initialize variables to keep track of the village with the highest data count and its count
let highestCount = 0;
let secondHighestCount = 0;
let thirdHighestCount = 0;

for (let i = 0; i < bandungVillages.length; i++) {
   let polyPoints = []

   for (let j = 0; j < bandungVillages[i].border.length; j++) {
      const lat = parseFloat(bandungVillages[i].border[j][1]);
      const lng = parseFloat(bandungVillages[i].border[j][0]);
      if ((!isNaN(lat) && typeof lat != typeof null) && !isNaN(lng) && typeof lng != typeof null) {
         polyPoints.push([lat, lng])
      }
   }

   $.ajax({
      url: `/getCountKelurahan/${bandungVillages[i].village}`,
      type: 'GET',
      dataType: 'json',
      success: function(data) {
         // ... (your existing code)
         console.log(data.countRekomendasiBantuanPendidikan[0].counted)

         // Set polygon fill color based on recommendation count
         let color = '';
         if (data.countRekomendasiBantuanPendidikan[0].counted > 5) {
            color = '#618264'
         } else if (data.countRekomendasiBantuanPendidikan[0].counted < 5) {
            color = '#B0D9B1'
         } else {
            color = '#D0E7D2'
         }

         // Create the polygon for the village with the determined color
         var kelurahanArea = new L.polyline(polyPoints, {
            width: 3,
            fill: true,
            fillOpacity: 0.7,
            fillColor: color
         }).addTo(map);

         const coordinates = kelurahanArea.getBounds().getCenter();

         const marker = new L.marker([coordinates.lat, coordinates.lng], {
            icon: L.divIcon({
              html: `<p  style="color: black;">${bandungVillages[i].village}</p>`,
              className: 'kelurahan-text',
              iconSize: [40, 10 ]
            })
         }).addTo(map);
         $('#legend').appendTo(map.getContainer());

         marker.on('click', function() {
            $.ajax({
               url: `/getCountKelurahan/${bandungVillages[i].village}`,
               type: 'GET',
               dataType: 'json',
               success: function(data) {
                     console.log(data);
                     const villageName = bandungVillages[i].village;
                     const modalContent = `<strong>KELURAHAN ${villageName}</strong><br />`;
                     const seluruhData = `<strong>Seluruh data bantuan pendidikan: ${data.countRekomendasiBantuanPendidikan[0].counted}</strong><br />`;
                     const teruskanData = `<strong>Jumlah proses rekomendasi bantuan pendidikan: ${data.countRekomendasiBantuanPendidikanTeruskan[0].counted}</strong><br />`;
                     const selesaiData = `<strong>Jumlah Selesai rekomendasi bantuan pendidikan: ${data.countRekomendasiBantuanPendidikanSelesai[0].counted}</strong><br />`;
               
                     // Menambahkan atribut href ke tautan "Lihat Detail Kelurahan"
                     $('a.btn-primary').attr('href', `/grafikBp/${villageName}`);
               
                     // Mengisi konten modal
                     $('#modalContent').html(seluruhData);
                     $('#modalContentTeruskan').html(teruskanData);
                     $('#modalContentSelesai').html(selesaiData);
                     $('#markerModalLabel').html(modalContent);
               
                     // Menampilkan modal Bootstrap
                     $('#markerModal').modal('show');
               },
               
               error: function(xhr, status, error) {
                  console.log(xhr.responseText);
               }
            });
         });
      },
      
      error: function(xhr, status, error) {
         console.log(xhr.responseText);
      }
   });
}






