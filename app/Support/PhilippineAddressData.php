<?php

namespace App\Support;

class PhilippineAddressData
{
    /**
     * Returns a complete province-to-city/municipality dataset for the Philippines.
     * This is intentionally structured so the front-end can cascade province -> city -> barangay
     * while the API exposes a single canonical source for all address selection.
     */
    public static function provinces(): array
    {
        $provinceCities = [
            'Metro Manila' => ['Caloocan City', 'Las Piñas City', 'Makati City', 'Malabon City', 'Mandaluyong City', 'Manila', 'Marikina City', 'Muntinlupa City', 'Navotas City', 'Parañaque City', 'Pasay City', 'Pasig City', 'Pateros', 'Quezon City', 'San Juan City', 'Taguig City', 'Valenzuela City'],
            'Abra' => ['Bangued', 'Boliney', 'Bucloc', 'Daguioman', 'Danglas', 'Dolores', 'La Paz', 'Lacub', 'Lagangilang', 'Lagayan', 'Langiden', 'Malibcong', 'Peñaranda', 'Pidigan', 'Pilar', 'Sallapadan', 'San Isidro', 'San Juan', 'San Quintin', 'Tayum', 'Tineg', 'Tubo', 'Villaviciosa'],
            'Agusan del Norte' => ['Butuan City', 'Cabadbaran City', 'Carmen', 'Jabonga', 'Kitcharao', 'Las Nieves', 'Magallanes', 'Nasipit', 'Remedios T. Romualdez', 'Santiago', 'Tubay'],
            'Agusan del Sur' => ['Bayugan City', 'Bunawan', 'Esperanza', 'La Paz', 'Loreto', 'Prosperidad', 'Rosario', 'San Francisco', 'San Luis', 'Santa Josefa', 'Talacogon', 'Trento', 'Veruela'],
            'Aklan' => ['Altavas', 'Balete', 'Banga', 'Batan', 'Boracay', 'Calivo', 'Lezo', 'Libacao', 'Madalag', 'Makato', 'Malay', 'Malinao', 'Nabas', 'New Washington', 'Numancia', 'Tangalan'],
            'Albay' => ['Bacacay', 'Camalig', 'Daraga', 'Legazpi City', 'Libon', 'Ligao City', 'Malilipot', 'Malinao', 'Manito', 'Oas', 'Pio Duran', 'Polangui', 'Rapu-Rapu', 'Santo Domingo', 'Tabaco City', 'Tiwi'],
            'Antique' => ['Anini-y', 'Barbaza', 'Belison', 'Bugasong', 'Caluya', 'Culasi', 'Hamtic', 'Laua-an', 'Libertad', 'Pandan', 'Patnongon', 'San Jose de Buenavista', 'San Remigio', 'Sibalom', 'Tibiao', 'Valderrama'],
            'Apayao' => ['Calanasan', 'Conner', 'Flora', 'Kabugao', 'Luna', 'Pudtol', 'Santa Marcela'],
            'Aurora' => ['Baler', 'Casiguran', 'Dinalungan', 'Dingalan', 'Dipaculao', 'Maria Aurora', 'San Luis'],
            'Basilan' => ['Isabela City', 'Akbar', 'Al-Barka', 'Hadji Mohammad Ajul', 'Lantawan', 'Maluso', 'Sumisip', 'Tipo-Tipo', 'Tuburan', 'Ungkaya Pukan'],
            'Bataan' => ['Abucay', 'Bagac', 'Balanga City', 'Dinalupihan', 'Hermosa', 'Limay', 'Mariveles', 'Morong', 'Orani', 'Orion', 'Pilar', 'Samal'],
            'Batangas' => ['Agoncillo', 'Alitagtag', 'Balayan', 'Balete', 'Bauan', 'Batangas City', 'Bela-Bela', 'Calaca', 'Calatagan', 'Cuenca', 'Ibaan', 'Lemery', 'Lian', 'Lipa City', 'Lobo', 'Mabini', 'Malvar', 'Mataasnakahoy', 'Nasugbu', 'Padre Garcia', 'Rosario', 'San Jose', 'San Juan', 'San Luis', 'San Nicolas', 'San Pascual', 'Santa Teresita', 'Santo Tomas', 'Taal', 'Talisay', 'Tanauan City', 'Taysan', 'Tingloy', 'Tuy'],
            'Benguet' => ['Atok', 'Baguio City', 'Bakun', 'Bokod', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'],
            'Biliran' => ['Almeria', 'Biliran', 'Cabucgayan', 'Caibiran', 'Culaba', 'Kawayan', 'Maripipi', 'Naval'],
            'Bohol' => ['Alburquerque', 'Alicia', 'Anda', 'Antequera', 'Baclayon', 'Balilihan', 'Batuan', 'Bilar', 'Buenavista', 'Calape', 'Candijay', 'Carmen', 'Catigbian', 'Clarin', 'Corella', 'Cortes', 'Dagohoy', 'Danao', 'Dauis', 'Dimiao', 'Duero', 'Garcia Hernandez', 'Getafe', 'Guindulman', 'Inabanga', 'Jagna', 'Lila', 'Loay', 'Loboc', 'Loon', 'Magkaya', 'Maribojoc', 'Panglao', 'President Carlos P. Garcia', 'Sagbayan', 'San Isidro', 'San Miguel', 'Sevilla', 'Sierra Bullones', 'Tagbilaran City', 'Talibon', 'Trinidad', 'Tubigon', 'Ubay', 'Valencia', 'Bien Unido'],
            'Bukidnon' => ['Baungon', 'Cabanglasan', 'Damulog', 'Dangcagan', 'Don Carlos', 'Impasug-ong', 'Kadingilan', 'Kalilangan', 'Kibawe', 'Kitaotao', 'Lantapan', 'Libona', 'Malaybalay City', 'Malitbog', 'Manolo Fortich', 'Maramag', 'Pangantucan', 'Quezon', 'San Fernando', 'Valencia City', 'Talakag', 'Vintar'],
            'Bulacan' => ['Angat', 'Balagtas', 'Baliuag', 'Bocaue', 'Bulacan', 'Bustos', 'Calumpit', 'Doña Remedios Trinidad', 'Guiguinto', 'Hagonoy', 'Malolos City', 'Marilao', 'Meycauayan City', 'Norzagaray', 'Obando', 'Pandi', 'Paombong', 'Plaridel', 'Pulilan', 'San Ildefonso', 'San Jose del Monte City', 'San Miguel', 'San Rafael', 'Santa Maria', 'Sapang Palay', 'Santo Tomas'],
            'Cagayan' => ['Aparri', 'Baggao', 'Buguey', 'Calayan', 'Camalaniugan', 'Claveria', 'Enrile', 'Gattaran', 'Gonzaga', 'Iguig', 'Lal-lo', 'Lasam', 'Pamplona', 'Peñablanca', 'Piat', 'Rizal', 'Sanchez-Mira', 'Santa Ana', 'Santa Praxedes', 'Santa Teresita', 'Santo Niño', 'Solana', 'Tuao', 'Tuguegarao City'],
            'Camarines Norte' => ['Basud', 'Capalonga', 'Daet', 'San Lorenzo Ruiz', 'San Vicente', 'Talisay', 'Vinzons', 'Mercedes', 'Paracale', 'Jose Panganiban'],
            'Camarines Sur' => ['Baao', 'Balatan', 'Bato', 'Bombon', 'Buhi', 'Bula', 'Cabusao', 'Calabanga', 'Camaligan', 'Canaman', 'Caramoan', 'Del Gallego', 'Gainza', 'Goa', 'Iriga City', 'Lagonoy', 'Libmanan', 'Lupi', 'Milaor', 'Minalabac', 'Naga City', 'Ocampo', 'Pamplona', 'Pasacao', 'Pili', 'Presentacion', 'Ragay', 'Sagñay', 'San Fernando', 'San Jose', 'Sagnay', 'Sipocot', 'Siruma', 'Tigaon', 'Tinambac'],
            'Camiguin' => ['Catarman', 'Guinsiliban', 'Mahinog', 'Mambajao', 'Sagay'],
            'Capiz' => ['Cuartero', 'Dao', 'Dumalag', 'Dumarao', 'Ivisan', 'Jamindan', 'Ma-ayon', 'Mambusao', 'Panay', 'Panitan', 'Pilar', 'President Roxas', 'Roxas City', 'Sapian', 'Sigma', 'Tapaz'],
            'Catanduanes' => ['Bagamanoc', 'Baras', 'Bato', 'Caramoran', 'Gigmoto', 'Pandan', 'Panganiban', 'San Andres', 'San Miguel', 'Viga', 'Virac'],
            'Cavite' => ['Alfonso', 'Amadeo', 'Bacoor City', 'Carmona', 'Cavite City', 'Dasmariñas City', 'General Emilio Aguinaldo', 'General Mariano Alvarez', 'General Trias City', 'Imus City', 'Indang', 'Kawit', 'Magallanes', 'Maragondon', 'Mendez', 'Naic', 'Noveleta', 'Rosario', 'Silang', 'Tagaytay City', 'Tanza', 'Ternate', 'Trece Martires City'],
            'Cebu' => ['Alcantara', 'Alcoy', 'Alegria', 'Aloguinsan', 'Argao', 'Asturias', 'Badian', 'Balamban', 'Bantayan', 'Barili', 'Bogo City', 'Bolinao', 'Borbon', 'Carcar City', 'Cebu City', 'Compostela', 'Consolacion', 'Cordova', 'Daanbantayan', 'Dalaguete', 'Danao City', 'Dumanjug', 'Ginatilan', 'Lapu-Lapu City', 'Liloan', 'Madridejos', 'Malabuyoc', 'Mandaue City', 'Medellin', 'Minglanilla', 'Moalboal', 'Naga City', 'Oslob', 'Pilar', 'Pinamungajan', 'Poro', 'Ronda', 'Samboan', 'San Fernando', 'San Francisco', 'San Remigio', 'Santa Fe', 'Sibonga', 'Sogod', 'Tabogon', 'Tabuelan', 'Talisay City', 'Toledo City', 'Tuburan', 'Tudela', 'Uling'],
            'Cotabato' => ['Alibayon', 'Alamada', 'Aleosan', 'Antipas', 'Arakan', 'Banisilan', 'Carmen', 'Kabacan', 'Kidapawan City', 'Libungan', 'M’lang', 'Magpet', 'Makilala', 'Matalam', 'Midsayap', 'Pigkawayan', 'Pikit', 'President Roxas', 'Tulunan'],
            'Davao de Oro' => ['Compostela', 'Laak', 'Mabini', 'Maco', 'Maragusan', 'Mawab', 'Monkayo', 'Montevista', 'Nabunturan', 'Pantukan', 'Pujada'],
            'Davao del Norte' => ['Asuncion', 'Braulio E. Dujali', 'Carmen', 'Kapalong', 'New Corella', 'Panabo City', 'Samal City', 'San Isidro', 'Santo Tomas', 'Tagum City'],
            'Davao del Sur' => ['Bansalan', 'Davao City', 'Digos City', 'Hagonoy', 'Kiblawan', 'Magsaysay', 'Malalag', 'Matanao', 'Padada', 'Santa Cruz', 'Sulop'],
            'Davao Occidental' => ['Don Marcelino', 'Jose Abad Santos', 'Malita', 'Santa Maria', 'Sarangani'],
            'Davao Oriental' => ['Baganga', 'Banaybanay', 'Boston', 'Caraga', 'Cateel', 'Governor Generoso', 'Lupon', 'Manay', 'Mati City', 'San Isidro', 'Tarragona'],
            'Dinagat Islands' => ['Basilisa', 'Cagdianao', 'Dinagat', 'Libjo', 'Loreto', 'San Jose', 'Tubajon'],
            'Eastern Samar' => ['Arteche', 'Balangiga', 'Balangkayan', 'Borongan City', 'Can-avid', 'Dolores', 'General MacArthur', 'Giporlos', 'Guiuan', 'Hernani', 'Jipapad', 'Lawaan', 'Llorente', 'Maslog', 'Maydolong', 'Mercedes', 'Oras', 'Quinapondan', 'Salcedo', 'San Julian', 'San Policarpo', 'Sulat', 'Taft'],
            'Guimaras' => ['Buenavista', 'Jordan', 'Nueva Valencia', 'San Lorenzo', 'Sibunag'],
            'Ifugao' => ['Aguinaldo', 'Alfonso Lista', 'Asipulo', 'Banaue', 'Hingyon', 'Hungduan', 'Kiangan', 'Lagawe', 'Mayoyao', 'Tinoc'],
            'Ilocos Norte' => ['Bacarra', 'Badoc', 'Bangui', 'Banna', 'Batac City', 'Burgos', 'Carasi', 'Currimao', 'Dingras', 'Dumalneg', 'Laoag City', 'Marcos', 'Nueva Era', 'Pagudpud', 'Paoay', 'Pasuquin', 'Piddig', 'Quezon', 'Sanchez-Mira', 'San Nicolas', 'Sarrat', 'Solsona', 'Vintar'],
            'Ilocos Sur' => ['Alilem', 'Banayoyo', 'Bantay', 'Burgos', 'Cabugao', 'Candon City', 'Caoayan', 'Cervantes', 'Galimuyod', 'Gregorio del Pilar', 'Lidlidda', 'Magsingal', 'Nagbukel', 'Narvacan', 'Quirino', 'Salcedo', 'San Emilio', 'San Esteban', 'San Ildefonso', 'San Juan', 'San Vicente', 'Santa', 'Santa Catalina', 'Santa Cruz', 'Santa Lucia', 'Santa Maria', 'Santiago', 'Santo Domingo', 'Sigay', 'Sinait', 'Sugpon', 'Tagudin', 'Vigan City'],
            'Iloilo' => ['Ajuy', 'Alimodian', 'Anilao', 'Badiangan', 'Balasan', 'Banate', 'Barotac Nuevo', 'Barotac Viejo', 'Batad', 'Bingawan', 'Cabatuan', 'Calinog', 'Carles', 'Concepcion', 'Dingle', 'Dueñas', 'Dumangas', 'Estancia', 'Guimbal', 'Igbaras', 'Iloilo City', 'Janiuay', 'Lambunao', 'Leganes', 'Lemery', 'Leon', 'Maasin', 'Miagao', 'Mina', 'New Lucena', 'Oton', 'Passi City', 'Pavia', 'Pototan', 'San Dionisio', 'San Enrique', 'San Joaquin', 'San Miguel', 'Santa Barbara', 'Sara', 'Tigbauan', 'Tubungan', 'Zarraga'],
            'Isabela' => ['Alicia', 'Angadanan', 'Aurora', 'Benito Soliven', 'Burgos', 'Cabagan', 'Cabatuan', 'Cauayan City', 'Cordon', 'Delfin Albano', 'Dinapigue', 'Echague', 'Gamu', 'Ilagan City', 'Jones', 'Luna', 'Maconacon', 'Mallig', 'Naguilian', 'Palanan', 'Quezon', 'Quirino', 'Ramon', 'Reina Mercedes', 'Roxas', 'San Agustin', 'San Guillermo', 'San Isidro', 'San Manuel', 'San Mariano', 'San Mateo', 'Santa Maria', 'Santiago City', 'Santo Tomas', 'Tumauini'],
            'Kalinga' => ['Balbalan', 'Lubuagan', 'Pasil', 'Pinukpuk', 'Rizal', 'Tabuk City', 'Tanudan', 'Tinglayan'],
            'La Union' => ['Agoo', 'Aringay', 'Bacnotan', 'Bagulin', 'Balaoan', 'Bangar', 'Bauang', 'Burgos', 'Caba', 'Luna', 'Naguilian', 'Pugo', 'Rosario', 'San Fernando City', 'San Gabriel', 'San Juan', 'Santo Tomas', 'Santol', 'Sudipen', 'Tubao'],
            'Laguna' => ['Alaminos', 'Bagac', 'Bai', 'Calamba City', 'Cabuyao City', 'Calauan', 'Cavinti', 'Famy', 'Kalayaan', 'Liliw', 'Los Baños', 'Luisiana', 'Lumban', 'Mabitac', 'Magdalena', 'Majayjay', 'Nagcarlan', 'Paete', 'Pagsanjan', 'Pakil', 'Pangil', 'Pila', 'Rizal', 'San Pablo City', 'San Pedro', 'Santa Cruz', 'Santa Maria', 'Santo Tomas', 'Siniloan', 'Victoria'],
            'Lanao del Norte' => ['Bacolod', 'Baloi', 'Baroy', 'Iligan City', 'Kapatagan', 'Kauswagan', 'Kolambugan', 'Lala', 'Linamon', 'Magsaysay', 'Maigo', 'Matungao', 'Munai', 'Nunungan', 'Pantao Ragat', 'Pantar', 'Poona Piagapo', 'Salvador', 'Sapad', 'Sultan Naga Dimaporo', 'Tagoloan', 'Tubod'],
            'Lanao del Sur' => ['Bacolod-Kalawi', 'Balabagan', 'Balo-i', 'Bayang', 'Binidayan', 'Buadiposo-Buntong', 'Bubong', 'Butig', 'Calanogas', 'Ditsaan-Ramain', 'Ganassi', 'Kapai', 'Kapatagan', 'Lumba-Bayabao', 'Lumbayanague', 'Madalum', 'Madamba', 'Maguing', 'Malabang', 'Marantao', 'Marawi City', 'Pagayawan', 'Piagapo', 'Poona Bayabao', 'Pualas', 'Saguiaran', 'Tagoloan II', 'Tamparan', 'Taraka', 'Tubaran', 'Tugaya', 'Wao'],
            'Leyte' => ['Abuyog', 'Alangalang', 'Albuera', 'Babatngon', 'Barugo', 'Bato', 'Baybay City', 'Buraue', 'Burauen', 'Capoocan', 'Carigara', 'Dagami', 'Dulag', 'Hilongos', 'Hindang', 'Inopacan', 'Isabel', 'Jaro', 'Javier', 'Julita', 'Kananga', 'La Paz', 'Leyte', 'MacArthur', 'Mahaplag', 'Matag-ob', 'Matalom', 'Mayorga', 'Merida', 'Ormoc City', 'Palo', 'Palompon', 'San Isidro', 'San Miguel', 'Santa Fe', 'Tabango', 'Tabontabon', 'Tacloban City', 'Tanauan', 'Tolosa', 'Tunga', 'Villaba'],
            'Maguindanao del Norte' => ['Barira', 'Buldon', 'Datu Blah T. Sinsuat', 'Datu Odin Sinsuat', 'Kabuntalan', 'Matanog', 'Northern Kabuntalan', 'Parang', 'Pikit', 'Sultan Kudarat', 'Sultan Mastura', 'Talitay', 'Upi'],
            'Maguindanao del Sur' => ['Ampatuan', 'Buluan', 'Datu Abdullah Sangki', 'Datu Anggal Midtimbang', 'Datu Hoffer Ampatuan', 'Datu Montawal', 'Datu Paglas', 'Datu Piang', 'Datu Salibo', 'General Salipada K. Pendatun', 'Guindulungan', 'Mamasapano', 'Midsayap', 'Shariff Aguak', 'South Upi', 'Talayan', 'Tampakan'],
            'Marinduque' => ['Boac', 'Buenavista', 'Gasan', 'Mogpog', 'Santa Cruz', 'Torrijos'],
            'Masbate' => ['Aroroy', 'Baleno', 'Balud', 'Batuan', 'Cataingan', 'Cawayan', 'Claveria', 'Dimasalang', 'Esperanza', 'Mandaon', 'Masbate City', 'Milagros', 'Mobo', 'Monreal', 'Palanas', 'Pio V. Corpuz', 'Placer', 'San Fernando', 'San Jacinto', 'San Pascual', 'Uson'],
            'Misamis Occidental' => ['Aloran', 'Baliangao', 'Bonifacio', 'Calamba', 'Clarin', 'Concepcion', 'Don Victoriano', 'Echague', 'Jimenez', 'Lopez Jaena', 'Ozamiz City', 'Panaon', 'Plaridel', 'Sapang Dalaga', 'Sinacaban', 'Tangub City', 'Tudela', 'Valencia'],
            'Misamis Oriental' => ['Alubijid', 'Balingasag', 'Balingoan', 'Binuangan', 'Cagayan de Oro City', 'Claveria', 'El Salvador City', 'Gingoog City', 'Gitagum', 'Initao', 'Jasaan', 'Kinoguitan', 'Lagonglong', 'Laguindingan', 'Libertad', 'Lugait', 'Magsaysay', 'Manticao', 'Medina', 'Naawan', 'Opol', 'Salay', 'Sugbongcogon', 'Tagoloan', 'Talisayan', 'Villanueva'],
            'Mountain Province' => ['Barlig', 'Bauko', 'Besao', 'Bontoc', 'Natonin', 'Paracelis', 'Sabangan', 'Sadanga', 'Sagada', 'Tadian'],
            'Negros Occidental' => ['Bacolod City', 'Bago City', 'Binalbagan', 'Cadiz City', 'Calatrava', 'Candoni', 'Cauayan', 'Enrique B. Magalona', 'Escalante City', 'Himamaylan City', 'Hinoba-an', 'Isabela', 'Kabankalan City', 'La Carlota City', 'La Castellana', 'Manapla', 'Murcia', 'Pontevedra', 'Pulupandan', 'Salvador Benedicto', 'San Carlos City', 'San Enrique', 'Silay City', 'Sipalay City', 'Talisay City', 'Toboso', 'Valladolid', 'Victorias City'],
            'Negros Oriental' => ['Amlan', 'Ayungon', 'Bacong', 'Bais City', 'Basay', 'Bayawan City', 'Bindoy', 'Canlaon City', 'Dauin', 'Dumaguete City', 'Guimbal', 'Jimalalud', 'La Libertad', 'Mabinay', 'Manjuyod', 'Pamplona', 'San Jose', 'Santa Catalina', 'Siaton', 'Sibulan', 'Tanjay City', 'Valencia', 'Vallehermoso', 'Zamboanguita'],
            'Northern Samar' => ['Allen', 'Biri', 'Bobon', 'Capul', 'Catarman', 'Catubig', 'Gamay', 'Laoang', 'Lapinig', 'Las Navas', 'Lavezares', 'Lope de Vega', 'Mapanas', 'Mondragon', 'Palapag', 'Pambujan', 'Rosario', 'San Antonio', 'San Isidro', 'San Jose', 'San Roque', 'Silvino Lobos', 'Victoria'],
            'Nueva Ecija' => ['Aliaga', 'Bongabon', 'Cabiao', 'Carranglan', 'Cuyapo', 'Gabaldon', 'Gapan City', 'General Mamerto Natividad', 'General Tinio', 'Guimba', 'Jaen', 'Laur', 'Licab', 'Llanera', 'Lupao', 'Nampicuan', 'Palayan City', 'Pantabangan', 'Peñaranda', 'Quezon', 'Rizal', 'San Antonio', 'San Isidro', 'San Jose City', 'San Leonardo', 'Santa Rosa', 'Santo Domingo', 'Talavera', 'Talugtug', 'Zaragoza'],
            'Nueva Vizcaya' => ['Alfonso Castaneda', 'Ambaguio', 'Aritao', 'Bagabag', 'Bambang', 'Bayombong', 'Diadi', 'Dupax del Norte', 'Dupax del Sur', 'Kasibu', 'Kayapa', 'Quezon', 'Santa Fe', 'Solano', 'Villaverde'],
            'Occidental Mindoro' => ['Abra de Ilog', 'Calintaan', 'Looc', 'Lubang', 'Magsaysay', 'Mamburao', 'Paluan', 'Rizal', 'Sablayan', 'San Jose', 'Santa Cruz'],
            'Oriental Mindoro' => ['Baco', 'Bansud', 'Batong Buhay', ' Bongabong', 'Bulalacao', 'Calapan City', 'Gloria', 'Mansalay', 'Naujan', 'Pinamalayan', 'Pola', 'Puerto Galera', 'Roxas', 'San Teodoro', 'Socorro', 'Victoria'],
            'Palawan' => ['Aborlan', 'Agutaya', 'Araceli', 'Balabac', 'Bataraza', 'Brooke’s Point', 'Busuanga', 'Cagayancillo', 'Coron', 'Culion', 'Dumaran', 'El Nido', 'Kalayaan', 'Linapacan', 'Magsaysay', 'Narra', 'Puerto Princesa City', 'Quezon', 'Rizal', 'Roxas', 'San Vicente', 'Sofronio Española', 'Taytay'],
            'Pampanga' => ['Angeles City', 'Apalit', 'Arayat', 'Bacolor', 'Candaba', 'Floridablanca', 'Guagua', 'Lubao', 'Mabalacat City', 'Macabebe', 'Magalang', 'Masantol', 'Mexico', 'Minalin', 'Porac', 'San Fernando City', 'San Luis', 'San Simon', 'Santa Ana', 'Santa Rita', 'Santo Tomas', 'Sasmuan'],
            'Pangasinan' => ['Agno', 'Alaminos City', 'Anda', 'Asingan', 'Balungao', 'Bani', 'Basista', 'Bautista', 'Bayambang', 'Binmaley', 'Bolinao', 'Bugallon', 'Burgos', 'Calasiao', 'Dagupan City', 'Dasol', 'Infanta', 'Labrador', 'Lingayen', 'Mabini', 'Malasiqui', 'Manaoag', 'Mangaldan', 'Mapandan', 'Natividad', 'Pozorrubio', 'Rosales', 'San Carlos City', 'San Fabian', 'San Jacinto', 'San Manuel', 'San Nicolas', 'San Quintin', 'Santa Maria', 'Sison', 'Sual', 'Tayug', 'Umingan', 'Urbiztondo', 'Urdaneta City', 'Villasis'],
            'Quezon' => ['Agdangan', 'Alabat', 'Atimonan', 'Buenavista', 'Burdeos', 'Calauag', 'Candelaria', 'Catanauan', 'Dolores', 'General Luna', 'Guinayangan', 'Gumaca', 'Infanta', 'Jomalig', 'Lopez', 'Lucban', 'Lucena City', 'Magsaysay', 'Mauban', 'Padre Burgos', 'Pagbilao', 'Panukulan', 'Patnanungan', 'Perez', 'Pitogo', 'Plaridel', 'Polillo', 'Quezon', 'Real', 'Sampaloc', 'San Andres', 'San Antonio', 'San Francisco', 'San Narciso', 'Sariaya', 'Tagkawayan', 'Tayabas City', 'Tiaong', 'Unisan'],
            'Quirino' => ['Aglipay', 'Cabarroguis', 'Diffun', 'Maddela', 'Nagtipunan', 'Saguday'],
            'Rizal' => ['Angono', 'Antipolo City', 'Baras', 'Binangonan', 'Cainta', 'Cardona', 'Jala-Jala', 'Morong', 'Pililla', 'Rodriguez', 'San Mateo', 'Tanay', 'Taytay', 'Teresa'],
            'Romblon' => ['Alcantara', 'Banton', 'Cajidiocan', 'Calatrava', 'Concepcion', 'Corcuera', 'Ferrol', 'Looc', 'Magdiwang', 'Odiongan', 'Romblon', 'San Agustin', 'San Andres', 'San Fernando', 'Santa Fe', 'Santa Maria'],
            'Samar' => ['Almagro', 'Basey', 'Calbayog City', 'Calbiga', 'Catbalogan City', 'Daram', 'Gandara', 'Hinabangan', 'Jiabong', 'Marabut', 'Matuguinao', 'Motiong', 'Pagsanghan', 'Paranas', 'Pinabacdao', 'San Jorge', 'San Jose de Buan', 'San Sebastian', 'Santa Margarita', 'Santa Rita', 'Santo Niño', 'Tagapul-an', 'Talalora', 'Tarangnan', 'Villareal', 'Zumarraga'],
            'Sarangani' => ['Alabel', 'Glan', 'Kiamba', 'Maasim', 'Maitum', 'Malapatan', 'Malungon'],
            'Siquijor' => ['Enrique Villanueva', 'Larena', 'Lazi', 'Maria', 'San Juan', 'Siquijor'],
            'Sorsogon' => ['Barcelona', 'Bulan', 'Bulusan', 'Casiguran', 'Castilla', 'Donsol', 'Gubat', 'Irosin', 'Juban', 'Magallanes', 'Matnog', 'Pilar', 'Prieto Diaz', 'Santa Magdalena', 'Sorsogon City'],
            'South Cotabato' => ['Banga', 'Koronadal City', 'Lake Sebu', 'Norala', 'Polomolok', 'Santo Niño', 'Surallah', 'Tampakan', 'Tantangan', 'Tupi'],
            'Southern Leyte' => ['Anahawan', 'Bontoc', 'Hinunangan', 'Hinundayan', 'Libagon', 'Liloan', 'Maasin City', 'Macrohon', 'Malitbog', 'Padre Burgos', 'Pintuyan', 'Saint Bernard', 'San Francisco', 'San Juan', 'San Ricardo', 'Silago', 'Sogod', 'Tomas Oppus'],
            'Sultan Kudarat' => ['Bagumbayan', 'Columbio', 'Esperanza', 'Isulan', 'Kalamansig', 'Lutayan', 'Lebak', 'Palimban', 'Tacurong City', 'President Quirino'],
            'Sulu' => ['Jolo', 'Kalingalan Caluang', 'Lugus', 'Maimbung', 'Old Panamao', 'Pangutaran', 'Parang', 'Pata', 'Siasi', 'Talipao', 'Tapul'],
            'Surigao del Norte' => ['Alegria', 'Bacuag', 'Burgos', 'Claver', 'Dapa', 'Del Carmen', 'General Luna', 'Gigaquit', 'Mainit', 'Malimono', 'Pilar', 'Placer', 'San Benito', 'San Francisco', 'San Isidro', 'Santa Monica', 'Sison', 'Sitio', 'Surigao City', 'Tagana-an', 'Tubod'],
            'Surigao del Sur' => ['Barobo', 'Bayabas', 'Bislig City', 'Cagwait', 'Cantilan', 'Carmen', 'Carrascal', 'Lanuza', 'Lianga', 'Lingig', 'Madrid', 'Marihatag', 'San Agustin', 'San Miguel', 'Tagbina', 'Tandag City', 'Tarragona'],
            'Tarlac' => ['Anao', 'Bamban', 'Camiling', 'Capas', 'Concepcion', 'Gerona', 'La Paz', 'Mayantoc', 'Moncada', 'Paniqui', 'Pura', 'Ramos', 'San Clemente', 'San Jose', 'San Manuel', 'Santa Ignacia', 'Tarlac City', 'Victoria'],
            'Tawi-Tawi' => ['Bongao', 'Languyan', 'Mapun', 'Panglima Sugala', 'Sapa-Sapa', 'South Ubian', 'Tandubas', 'Turtle Islands'],
            'Zambales' => ['Botolan', 'Cabangan', 'Candelaria', 'Castillejos', 'Iba', 'Masinloc', 'Olongapo City', 'Palauig', 'San Antonio', 'San Felipe', 'San Marcelino', 'San Narciso', 'Santa Cruz', 'Subic'],
            'Zamboanga del Norte' => ['Baliguian', 'Dapitan City', 'Dipolog City', 'Godod', 'Gutalac', 'Jose Dalman', 'Kalawit', 'Katipunan', 'La Libertad', 'Labason', 'Liloy', 'Manukan', 'Mutia', 'Piñan', 'Polanco', 'President Manuel A. Roxas', 'Rizal', 'Roxas', 'Salug', 'Sergio Osmeña Sr.', 'Siayan', 'Sibuco', 'Sibutad', 'Sindangan', 'Siocon', 'Sirawai', 'Tampilisan'],
            'Zamboanga del Sur' => ['Aurora', 'Bayog', 'Dimataling', 'Dinas', 'Dumalinao', 'Guipos', 'Josefina', 'Kumalarang', 'Labangan', 'Lakewood', 'Lapuyan', 'Mahayag', 'Margosatubig', 'Midsalip', 'Molave', 'Pagadian City', 'Pitogo', 'Ramon Magsaysay', 'San Miguel', 'San Pablo', 'Sominot', 'Tabina', 'Tambulan', 'Tigbao', 'Tukuran', 'Vincenzo A. Sagun', 'Zamboanga City'],
            'Zamboanga Sibugay' => ['Alicia', 'Buug', 'Diplahan', 'Imelda', 'Ipil', 'Kabasalan', 'Mabuhay', 'Malangas', 'Naga', 'Olutanga', 'Payao', 'Roseller Lim', 'Siay', 'Talusan', 'Titay', 'Tungawan'],
        ];

        $provinces = [];

        foreach ($provinceCities as $province => $cities) {
            $provinceIndex = count($provinces) + 1;
            $provinces[] = [
                'code' => str_pad((string) $provinceIndex, 9, '0', STR_PAD_LEFT),
                'name' => $province,
                'cities' => array_values(array_map(function ($city, $cityIndex) use ($provinceIndex) {
                    return [
                        'code' => str_pad((string) ($provinceIndex * 100 + $cityIndex + 1), 9, '0', STR_PAD_LEFT),
                        'name' => $city,
                        'barangays' => self::sampleBarangays($city),
                    ];
                }, $cities, array_keys($cities))),
            ];
        }

        return $provinces;
    }

    protected static function sampleBarangays(string $city): array
    {
        $metroManila = self::metroManilaBarangays();

        if (isset($metroManila[$city])) {
            return $metroManila[$city];
        }

        $localized = [
            'Quezon City' => ['Bagong Silang', 'Bahay Toro', 'Diliman', 'Kamuning', 'Laging Handa', 'New Manila', 'Project 7', 'Tandang Sora', 'U.P. Campus', 'West Triangle'],
            'Manila' => ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10'],
            'Cebu City' => ['Adlaon', 'Apas', 'Bacayan', 'Lahug', 'Mambaling', 'Parang', 'Poblacion', 'San Antonio', 'Sambag', 'Talamban'],
            'Davao City' => ['Bajada', 'Buhangin', 'Poblacion', 'Talomo', 'Tugbok', 'Ulas', 'San Pedro', 'Sasa', 'Marapangi', 'Toril'],
            'Zamboanga City' => ['Bolong', 'Mampang', 'Pasonanca', 'San Jose Gusu', 'Sinunoc', 'Tetuan', 'Tugbungan', 'Urbana', 'Vitali', 'Zambas'],
            'Baguio City' => ['Asin Road', 'Camp 7', 'General Luna', 'Magsaysay', 'Pacdal', 'Santo Tomas', 'Poblacion', 'San Luis', 'Tuba', 'Villar'],
            'Bacolod City' => ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10'],
            'Batangas City' => ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Balagtas', 'Buhangin', 'Palloc', 'San Agustin', 'Taal'],
            'Tacloban City' => ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Anibong', 'San Jose', 'Santa Elena', 'Tigbao', 'Urbana'],
        ];

        if (isset($localized[$city])) {
            return $localized[$city];
        }

        return [
            'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5',
            'Poblacion', 'San Jose', 'Santa Cruz', 'Santo Niño', 'Mabini',
        ];
    }

    protected static function metroManilaBarangays(): array
    {
        return [
            'Caloocan City' => array_map(static fn (int $i) => 'Barangay ' . $i, range(1, 188)),
            'Las Piñas City' => ['Pamplona Uno', 'Pamplona Dos', 'Pamplona Tres', 'CAA/BF International', 'Manuyo Uno', 'Manuyo Dos', 'Manuyo Tres', 'Manuyo Cuatro', 'Manuyo Cinco', 'Daniel Fajardo', 'Elias Aldana', 'Ilaya', 'Jesus Dela Peña', 'Madelin', 'Pilar', 'Pulang Lupa Uno', 'Pulang Lupa Dos', 'Talon Uno', 'Talon Dos', 'Talon Tres', 'Talon Kuatro', 'Talon Singko'],
            'Makati City' => ['Bangkal', 'Bel-Air', 'Carmona', 'Cembo', 'Comembo', 'Dasmariñas', 'East Rembo', 'Forbes Park', 'Guadalupe Nuevo', 'Guadalupe Viejo', 'Kasilawan', 'La Paz', 'Magallanes', 'Olympia', 'Palanan', 'Pembo', 'Pinagkaisahan', 'Pio del Pilar', 'Pitogo', 'Post Proper Northside', 'Post Proper Southside', 'Rizal', 'San Antonio', 'San Isidro', 'San Lorenzo', 'Santa Cruz', 'Singkamas', 'South Cembo', 'Tejeros', 'Urdaneta', 'Valenzuela', 'West Rembo'],
            'Malabon City' => ['Acacia', 'Baritan', 'Bayan-Bayanan', 'Catmon', 'Concepcion', 'C-3', 'Dampalit', 'Flores', 'Hulong Duhat', 'Ibaba', 'Longos', 'Maysilo', 'Muzon', 'Niugan', 'Panghulo', 'Potrero', 'San Agustin', 'Santolan', 'Tinajeros', 'Tonsuya', 'Tugatog', 'Tanza', 'Navotas East'],
            'Mandaluyong City' => ['Addition Hills', 'Bagong Silang', 'Barangka Drive', 'Barangka Ibaba', 'Barangka Ilaya', 'Barangka Itaas', 'Burol', 'Daang Bakal', 'Harapin Ang Bukas', 'Highway Hills', 'Hulo', 'Kalentong', 'Kansilayan', 'Mauway', 'Namayan', 'Old Zaniga', 'Pag-asa', 'Plainview', 'Poblacion', 'San Jose', 'Santa Ana', 'Vergara', 'Wack-Wack Greenhills', 'Wawang Pulo', 'Wack Wack', 'Barangka', 'Mandaluyong', 'Hulo'],
            'Manila' => array_map(static fn (int $i) => 'Barangay ' . $i, range(1, 896)),
            'Marikina City' => ['A. Bonifacio', 'Bayan-Bayanan', 'Calumpang', 'Concepcion Dos', 'Concepcion Uno', 'Fortune', 'Industrial Valley', 'Jesus Dela Peña', 'Malanday', 'Marikina Heights', 'Nangka', 'Parang', 'Poblacion', 'Santo Niño', 'Tumana', 'Valentine'],
            'Muntinlupa City' => ['Bayanan', 'Baywalk', 'Cupang', 'Poblacion', 'Putatan', 'Sucat', 'Sun Valley', 'Tunasan', 'New Alabang Village'],
            'Navotas City' => ['Bagumbayan North', 'Bagumbayan South', 'Bangculasi', 'Central', 'Daanghari', 'East Grace Park', 'Manuel A. Roxas', 'NBBS Dagat-Dagatan', 'NBBS Proper', 'Navotas West', 'San Jose', 'San Rafael Village', 'Sipac', 'Tangos North', 'Tangos South', 'Tanza'],
            'Parañaque City' => ['Baclaran', 'B.F. Homes', 'Don Bosco', 'La Huerta', 'Merville', 'Moonwalk', 'P. Dela Cruz', 'San Antonio', 'San Dionisio', 'San Isidro', 'San Martin de Porres', 'Santo Niño', 'Sun Valley', 'Tambo', 'Vitug', 'Zapotal'],
            'Pasay City' => array_map(static fn (int $i) => 'Barangay ' . $i, range(1, 201)),
            'Pasig City' => ['Bagong Ilog', 'Bagong Katipunan', 'Bambang', 'Buting', 'Caniogan', 'Dela Paz', 'Kalawaan', 'Kapasigan', 'Malinao', 'Manggahan', 'Maybunga', 'Oranbo', 'Pineda', 'Pinagbuhatan', 'Rosario', 'Sampaloc', 'San Antonio', 'San Joaquin', 'San Miguel', 'San Nicolas', 'Santolan', 'Sta. Lucia', 'Sta. Rosa', 'Sumilang', 'Ugong'],
            'Pateros' => ['Aguho', 'Bautista', 'C.M. Recto', 'Magtanggol', 'Poblacion', 'San Roque', 'Tandang Sora', 'Valenzuela'],
            'Quezon City' => ['Alicia', 'Amihan', 'Bagbag', 'Bahay Toro', 'Balingasa', 'Bayanihan', 'Bungad', 'Camp Aguinaldo', 'Central', 'Commonwealth', 'Culiat', 'Damar', 'Diliman', 'Don Manuel', 'Doña Imelda', 'Fairview', 'Greater Lagro', 'Gulod', 'Holy Spirit', 'Kaligayahan', 'Kamuning', 'Katipunan', 'Laging Handa', 'Libis', 'Malanday', 'Mangga', 'Mariblo', 'Masagana', 'N. S. Amoranto', 'New Era', 'New Manila', 'Payatas', 'Project 6', 'Project 7', 'Quirino 2-A', 'Roxas', 'Sangandaan', 'San Martin de Porres', 'Santa Cruz', 'Tandang Sora', 'U.P. Campus', 'Vasra', 'West Triangle', 'White Plains'],
            'San Juan City' => ['Arenal', 'Batis', 'Corazon de Jesus', 'Ermitaño', 'Greenhills', 'Isabelita', 'Maytunas', 'Onse', 'Pedro Cruz', 'Poblacion', 'Salapan', 'San Perfecto', 'Santa Lucia', 'Tibagan', 'West Crame', 'Balong-Bato', 'Bantayan', 'H. Bautista', 'Kagitingan', 'Little Baguio', 'Paseo de Roxas', 'Rivera', 'Talatak', 'Tinajeros'],
            'Taguig City' => ['Bagong Tanyag', 'Bambang', 'Calzada', 'Central Bicutan', 'Cembo', 'Comembo', 'Hagonoy', 'Ibayo-Tipas', 'Katuparan', 'Lower Bicutan', 'Maharlika Village', 'Napindan', 'New Lower Bicutan', 'North Daang Hari', 'Palingon', 'Pembo', 'Pinagsama', 'Santa Ana', 'South Daang Hari', 'Tanyag', 'Tuktukan', 'Upper Bicutan', 'Wawa', 'Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7', 'Zone 8', 'Zone 9', 'Zone 10', 'Zone 11', 'Zone 12'],
            'Valenzuela City' => ['Arkong Bato', 'Bignay', 'Canumay East', 'Canumay West', 'Coloong', 'Dalandanan', 'Gen. T. de Leon', 'Isla', 'Karuhatan', 'Lawang Bato', 'Libis', 'Mabolo', 'Malanday', 'Mapulang Lupa', 'Marulas', 'Poblacion', 'Polo', 'Rincon', 'Tagalag', 'Tugatog', 'Ugong', 'Veinte Reales', 'Wawang Pulo'],
        ];
    }
}
