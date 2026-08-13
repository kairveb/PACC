@props([
    'provinceName' => 'address_province',
    'cityName' => 'address_city',
    'barangayName' => 'address_barangay',
    'provinceValue' => null,
    'cityValue' => null,
    'barangayValue' => null,
    'provinceLabel' => 'Province',
    'cityLabel' => 'City / Municipality',
    'barangayLabel' => 'Barangay',
])

@php
    $componentId = 'phil-address-' . uniqid();
    $provinceInputId = $componentId . '-province';
    $cityInputId = $componentId . '-city';
    $barangayInputId = $componentId . '-barangay';
    $provinceListId = $componentId . '-province-list';
    $cityListId = $componentId . '-city-list';
    $barangayListId = $componentId . '-barangay-list';
    $provinceOldKey = str_replace(['[', ']'], ['.', ''], $provinceName);
    $cityOldKey = str_replace(['[', ']'], ['.', ''], $cityName);
    $barangayOldKey = str_replace(['[', ']'], ['.', ''], $barangayName);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4" data-philippine-address-root>
    <div>
        <label for="{{ $provinceInputId }}" class="block text-sm font-medium text-slate-700 mb-1">{{ $provinceLabel }}</label>
        <input
            id="{{ $provinceInputId }}"
            type="text"
            name="{{ $provinceName }}"
            list="{{ $provinceListId }}"
            value="{{ old($provinceOldKey, $provinceValue ?? '') }}"
            placeholder="Type or select a province"
            data-address-field="province"
            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
        >
        <datalist id="{{ $provinceListId }}"></datalist>
    </div>

    <div>
        <label for="{{ $cityInputId }}" class="block text-sm font-medium text-slate-700 mb-1">{{ $cityLabel }}</label>
        <input
            id="{{ $cityInputId }}"
            type="text"
            name="{{ $cityName }}"
            list="{{ $cityListId }}"
            value="{{ old($cityOldKey, $cityValue ?? '') }}"
            placeholder="Type or select a city"
            data-address-field="city"
            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
            {{ old($provinceOldKey, $provinceValue ?? '') ? '' : 'disabled' }}
        >
        <datalist id="{{ $cityListId }}"></datalist>
    </div>

    <div class="md:col-span-2">
        <label for="{{ $barangayInputId }}" class="block text-sm font-medium text-slate-700 mb-1">{{ $barangayLabel }}</label>
        <input
            id="{{ $barangayInputId }}"
            type="text"
            name="{{ $barangayName }}"
            list="{{ $barangayListId }}"
            value="{{ old($barangayOldKey, $barangayValue ?? '') }}"
            placeholder="Type or select a barangay"
            data-address-field="barangay"
            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
            {{ old($cityOldKey, $cityValue ?? '') ? '' : 'disabled' }}
        >
        <datalist id="{{ $barangayListId }}"></datalist>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const philippineAddressData = {
                    'Metro Manila': {
                        'Caloocan City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Barangay 6','Barangay 7','Barangay 8','Barangay 9','Barangay 10'],
                        'Las Piñas City': ['Pilar','Talon Dos','Manuyo Dos','CAA','Zapotal','Pulang Lupa','Talon Uno','Elias Aldana','Ilaya','Madrigal'],
                        'Makati City': ['Bangkal','Bel-Air','Carmona','Poblacion','San Antonio','Urdaneta','Valenzuela','Kasilawan','Magallanes','Palanan'],
                        'Malabon City': ['Baritan','Concepcion','Flores','Muzon','Navotas','Potrero','Tonsuya','Tugatog','Catmon','Caniogan'],
                        'Mandaluyong City': ['Addition Hills','Bagong Silang','Barangka','Burol','Hulo','Maysilo','Namayan','Pag-asa','Plainview','Wack-Wack'],
                        'Manila': ['Binondo','Ermita','Intramuros','Malate','Paco','Quiapo','San Miguel','Santa Ana','Tondo','Sampaloc'],
                        'Marikina City': ['Barangka','Concepcion Dos','Jesus Dela Peña','Marikina Heights','Nangka','Parang','San Roque','Santa Elena','Tumana','Valle Verde'],
                        'Muntinlupa City': ['Ayala Alabang','Bayanan','Cupang','Poblacion','Sucat','Tunasan','Victoria Homes','Putatan','New Alabang Village','Niugan'],
                        'Navotas City': ['Bangkulasi','Daanghari','North Bay Blvd.','San Jose','Sipac','Tangos','Tanza','Navotas East','Navotas West','Panghulo'],
                        'Parañaque City': ['Baclaran','Don Bosco','Merville','San Antonio','Sun Valley','Tambo','La Huerta','San Dionisio','San Isidro','Vitalez'],
                        'Pasay City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Barangay 6','Barangay 7','Barangay 8','Barangay 9','Barangay 10'],
                        'Pasig City': ['Bagong Ilog','Buting','Caniogan','Kalawaan','Manggahan','Oranbo','Pineda','San Joaquin','Santa Barbara','Sumilang'],
                        'Quezon City': ['Bagong Silang','Bahay Toro','Diliman','Kamuning','Laging Handa','New Manila','Project 7','Tandang Sora','U.P. Campus','West Triangle'],
                        'San Juan City': ['Corazon de Jesus','Greenhills','Little Baguio','Pedro Cruz','San Perfecto','Santa Lucia','Tibagan','Maytunas','Halo-halo','Pinaglabanan'],
                        'Taguig City': ['Bagumbayan','Bicutan','Central Bicutan','Fort Bonifacio','Maharlika Village','North Signal','Pateros','Pinagsama','Tuktukan','Ususan'],
                        'Valenzuela City': ['Arkong Bato','Canumay','Dalandanan','Gen. T. De Leon','Karuhatan','Lawang Bato','Mabolo','Malinta','Polo','Wawang Pulo']
                    },
                    'Cavite': {
                        'Bacoor City': ['Bayanan','Campo Santo','Daang Bukid','Molino','Niog','Pulong'],
                        'Cavite City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Barangay 6','Barangay 7','Barangay 8','Barangay 9','Barangay 10'],
                        'Dasmariñas City': ['Burol','Salawag','Langkaan','San Agustin','Pala-Pala','Carmona'],
                        'General Trias City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Almanza','P. Rosales','San Francisco','Tejero'],
                        'Imus City': ['Anabu','Bucandala','Cabilang Baybay','Medicion','Poblacion','Toclong'],
                        'Tagaytay City': ['Calabuso','Kaybagal South','Mendez Crossing','Sangat','Tolentino West','San Jose'],
                        'Trece Martires City': ['Cabezas','De Ocampo','Conchu','San Agustin','Poblacion','Lallana']
                    },
                    'Laguna': {
                        'Biñan City': ['San Jose','Sto. Niño','Mabini','Santo Tomas','Poblacion','Malaban'],
                        'Calamba City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Parang','Putho-Tuntong','Real','Ma. Cruz','Turbina'],
                        'San Pablo City': ['San Rafael','San Jose','San Roque','Dolores','Concepcion','Bagong Bayan'],
                        'Santa Rosa City': ['Balibago','Caingin','Labas','Malitlit','Poblacion','Tagapo'],
                        'Lipa City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Bahay','Balete','Bolbok','Cumba','Mabini'],
                        'Cabuyao City': ['Banaybanay','Bigaa','Mamatid','Pulo','Sala','San Isidro']
                    },
                    'Bulacan': {
                        'Malolos City': ['Guinhawa','Mojon','Pinagbakahan','San Agustin','Santor','Tikay'],
                        'San Jose del Monte City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Francisco Homes','Minuyan','Poblacion','Tungkong Mangga','Gaya-Gaya'],
                        'Meycauayan City': ['Bagbaguin','Bahayang Pag-asa','Camalig','Lawa','Malanday','Pajo'],
                        'Baliwag': ['Bagong Pag-asa','Bahayang Pag-asa','Baliwag','Casilagan','Piel','San Jose'],
                        'Guiguinto': ['Cut-cut','Daang Bukid','Mabiga','Panginay','Santa Cruz','Tiaong']
                    },
                    'Pampanga': {
                        'Angeles City': ['Balibago','Cutcut','Pampang','Poblacion','Santo Rosario','Sapang Bato'],
                        'San Fernando City': ['Bahayang Pag-asa','Baliti','Dolores','Lourdes','Poro','Santa Catalina'],
                        'Mabalacat City': ['Bical','Dau','Mabiga','Malabanan','Mawaque','Poblacion'],
                        'Apalit': ['Capalangan','Cocolo','Paligui','Sampaloc','San Vicente','Tabuyuc'],
                        'Lubao': ['Babo Pangulo','Concepcion','Lourdes','Mabiga','Prado Siongco','Santa Catalina']
                    },
                    'Batangas': {
                        'Batangas City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Balagtas','Buhangin','Palloc','San Agustin','Taal'],
                        'Lipa City': ['Balete','Bolbok','Cumba','Mabini','Marawoy','San Carlos'],
                        'Tanauan City': ['Altura','Bagbag','Bilog','Calamias','Luyos','San Jose'],
                        'Nasugbu': ['Bucana','Lumbangan','Mabini','Poblacion','Tiaong','Wawa'],
                        'Taal': ['Banyaga','Gonzales','Poblacion','San Nicolas','Santo Niño','Tulo']
                    },
                    'Rizal': {
                        'Antipolo City': ['Bagong Nayon','Calawis','Dela Paz','Mambugan','San Jose','Sta. Cruz'],
                        'Cainta': ['A. Bonifacio','San Isidro','San Miguel','San Vicente','Santa Rosa','Sto. Niño'],
                        'Rodriguez': ['Burgos','Manggahan','San Jose','San Isidro','San Rafael','Sto. Niño'],
                        'Taytay': ['Dolores','San Isidro','San Juan','Santa Ana','Sta. Monica','Taytay'],
                        'Binangonan': ['Bilibiran','Kalinawan','Palangoy','Poblacion','San Isidro','Santa Ursula']
                    },
                    'Cebu': {
                        'Cebu City': ['Adlaon','Apas','Bacayan','Lahug','Mambaling','Parang'],
                        'Lapu-Lapu City': ['Bankal','Marigondon','Mactan','Pajo','Pusok','Tungasan'],
                        'Mandaue City': ['Alang-alang','Canduman','Labogon','Mango','Opao','Subangdaku'],
                        'Talisay City': ['Bulacao','Lutong','Poblacion','San Fernando','Tabunok','Tangke'],
                        'Danao City': ['Basak','Cambioc','Danao','Poblacion','Sabang','Sampok']
                    },
                    'Davao del Sur': {
                        'Davao City': ['Buhangin','Poblacion','Talomo','Tugbok','Ulas','Bajada'],
                        'Digos City': ['Aplaya','Goma','Kapatagan','Poblacion','San Jose','Sinawilan'],
                        'Matanao': ['Bansalan','Capas','Kabasalan','Poblacion','Sampao','Tomas Oppus'],
                        'Malita': ['Bito','Lais','Poblacion','Bulan','Magsaysay','Mansay']
                    },
                    'Iloilo': {
                        'Iloilo City': ['Arevalo','La Paz','Mandurriao','Molo','Jaro','City Proper'],
                        'Passi City': ['Agtabo','Bita-og','Poblacion','Rizal','Santo Tomas','Tabucan'],
                        'Jordan': ['Butuan','Lutong','Poblacion','San Miguel','Santa Barbara','Tigbauan'],
                        'Oton': ['Agham','Bagiw','Poblacion','Rizal','San Antonio','Tina']
                    },
                    'Negros Occidental': {
                        'Bacolod City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Barangay 6','Barangay 7','Barangay 8','Barangay 9','Barangay 10'],
                        'Silay City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Bagtic','E. Lopez','Guimbala-on','Mali-ao','Poblacion'],
                        'Bago City': ['Balingasag','Bucaya','Hilwan','Poblacion','Rizal','Taloc'],
                        'Kabankalan City': ['Camingawan','Cauayan','Poblacion','Sagasa','Talubangi','Tampalon']
                    },
                    'Leyte': {
                        'Tacloban City': ['Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Anibong','San Jose','Santa Elena','Tigbao','Urbana'],
                        'Ormoc City': ['Alegria','Bagong Buhay','Barangay 1','Cabaunan','Lao','Poblacion'],
                        'Baybay City': ['Bubon','Jebangga','Maanyag','Poblacion','San Isidro','Visca'],
                        'Maasin City': ['Bahay','Cabarbarran','Mambajao','Poblacion','Siloam','Tunga']
                    },
                    'Zamboanga del Sur': {
                        'Zamboanga City': ['Bolong','Mampang','Pasonanca','San Jose Gusu','Sinunoc','Tetuan'],
                        'Pagadian City': ['Balangasan','Benguet','Dumagal','Kawayan','Lourdes','Poblacion'],
                        'Dumingag': ['Biga','Cabulay','Malaubang','Poblacion','Tigwa','Tularan'],
                        'Molave': ['Baclay','Bubong','Mabini','Poblacion','Saguiran','Sungay']
                    },
                    'Benguet': {
                        'Baguio City': ['Asin Road','Camp 7','General Luna','Magsaysay','Pacdal','Santo Tomas'],
                        'La Trinidad': ['Alapang','Ba-asa','Poblacion','Shilan','Tawang','Wangal'],
                        'Itogon': ['Amlimay','Loacan','Poblacion','Santo Tomas','Upp','Virac'],
                        'Tuba': ['Ambiong','Camp 1','Nangka','Poblacion','Tadiangan','Tublay']
                    },
                    'Ilocos Norte': {
                        'Laoag City': ['Arroyo','Bgy. 1','Bgy. 2','Bgy. 3','Bgy. 4','Bulag','Poblacion','San Andres','Viga','Villar']
                    },
                    'Pangasinan': {
                        'Dagupan City': ['Bonuan Boquig','Bonuan Gueset','Lamarca','Mabini','Poblacion','Tapuac'],
                        'Urdaneta City': ['Anonas','Bayaoas','Mabaning','Poblacion','San Jose','Togonan'],
                        'San Carlos City': ['Balayong','Bacayao','Malalacao','Poblacion','San Juan','Tebeng']
                    }
                };

                const normalizeText = (value = '') => String(value).toLowerCase().replace(/[^a-z0-9\s]/gi, '').replace(/\s+/g, ' ').trim();

                const populateDatalist = (datalist, values = []) => {
                    if (!datalist) return;
                    datalist.innerHTML = '';
                    values.forEach((value) => {
                        const option = document.createElement('option');
                        option.value = value;
                        datalist.appendChild(option);
                    });
                };

                const initAddressGroup = (root) => {
                    const provinceInput = root.querySelector('[data-address-field="province"]');
                    const cityInput = root.querySelector('[data-address-field="city"]');
                    const barangayInput = root.querySelector('[data-address-field="barangay"]');

                    if (!provinceInput || !cityInput || !barangayInput) {
                        return;
                    }

                    const provinceList = root.querySelector('datalist');
                    const cityList = root.querySelectorAll('datalist')[1];
                    const barangayList = root.querySelectorAll('datalist')[2];
                    const provinceNames = Object.keys(philippineAddressData).sort();

                    populateDatalist(provinceList, provinceNames);

                    const syncCityOptions = () => {
                        const provinceValue = provinceInput.value.trim();
                        const match = Object.keys(philippineAddressData).find((province) => normalizeText(province) === normalizeText(provinceValue));
                        const selectedProvince = match || provinceValue;

                        if (!selectedProvince || !philippineAddressData[selectedProvince]) {
                            cityInput.disabled = true;
                            cityInput.value = '';
                            barangayInput.disabled = true;
                            barangayInput.value = '';
                            populateDatalist(cityList, []);
                            populateDatalist(barangayList, []);
                            cityInput.placeholder = 'Type or select a city';
                            barangayInput.placeholder = 'Type or select a barangay';
                            return;
                        }

                        const cityNames = Object.keys(philippineAddressData[selectedProvince]).sort();
                        cityInput.disabled = false;
                        populateDatalist(cityList, cityNames);
                        cityInput.placeholder = `Type or select a city in ${selectedProvince}`;

                        const currentCity = cityInput.value.trim();
                        if (!currentCity || !cityNames.some((city) => normalizeText(city) === normalizeText(currentCity))) {
                            cityInput.value = '';
                        }

                        const currentSelectedCity = cityInput.value.trim();
                        const barangayNames = currentSelectedCity && philippineAddressData[selectedProvince][currentSelectedCity]
                            ? philippineAddressData[selectedProvince][currentSelectedCity]
                            : [];

                        barangayInput.disabled = !currentSelectedCity || barangayNames.length === 0;
                        populateDatalist(barangayList, barangayNames);
                        barangayInput.placeholder = barangayNames.length ? `Type or select a barangay in ${currentSelectedCity}` : 'Type or select a barangay';

                        if (!currentSelectedCity) {
                            barangayInput.value = '';
                        }
                    };

                    const syncBarangayOptions = () => {
                        const provinceValue = provinceInput.value.trim();
                        const selectedProvince = Object.keys(philippineAddressData).find((province) => normalizeText(province) === normalizeText(provinceValue)) || provinceValue;
                        const cityValue = cityInput.value.trim();

                        if (!selectedProvince || !philippineAddressData[selectedProvince]) {
                            barangayInput.disabled = true;
                            barangayInput.value = '';
                            populateDatalist(barangayList, []);
                            return;
                        }

                        const cityNames = Object.keys(philippineAddressData[selectedProvince]).sort();
                        const selectedCity = cityNames.find((city) => normalizeText(city) === normalizeText(cityValue)) || cityValue;
                        const barangayNames = philippineAddressData[selectedProvince][selectedCity] || [];

                        barangayInput.disabled = !selectedCity || barangayNames.length === 0;
                        populateDatalist(barangayList, barangayNames);
                        barangayInput.placeholder = barangayNames.length ? `Type or select a barangay in ${selectedCity}` : 'Type or select a barangay';

                        if (!selectedCity) {
                            barangayInput.value = '';
                        }
                    };

                    provinceInput.addEventListener('input', syncCityOptions);
                    cityInput.addEventListener('input', syncBarangayOptions);

                    if (provinceInput.value.trim()) {
                        syncCityOptions();
                        if (cityInput.value.trim()) {
                            syncBarangayOptions();
                        }
                    }
                };

                const roots = document.querySelectorAll('[data-philippine-address-root]');
                roots.forEach(initAddressGroup);
            })();
        </script>
    @endpush
@endonce
