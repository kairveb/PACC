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
                const fallbackPhilippineAddressData = {
                    'Metro Manila': {
                        'Caloocan City': Array.from({ length: 188 }, (_, i) => `Barangay ${i + 1}`),
                        'Las Piñas City': ['Pamplona Uno', 'Pamplona Dos', 'Pamplona Tres', 'CAA/BF International', 'Manuyo Uno', 'Manuyo Dos', 'Manuyo Tres', 'Manuyo Cuatro', 'Manuyo Cinco', 'Daniel Fajardo', 'Elias Aldana', 'Ilaya', 'Jesus Dela Peña', 'Madelin', 'Pilar', 'Pulang Lupa Uno', 'Pulang Lupa Dos', 'Talon Uno', 'Talon Dos', 'Talon Tres', 'Talon Kuatro', 'Talon Singko'],
                        'Makati City': ['Bangkal', 'Bel-Air', 'Carmona', 'Cembo', 'Comembo', 'Dasmariñas', 'East Rembo', 'Forbes Park', 'Guadalupe Nuevo', 'Guadalupe Viejo', 'Kasilawan', 'La Paz', 'Magallanes', 'Olympia', 'Palanan', 'Pembo', 'Pinagkaisahan', 'Pio del Pilar', 'Pitogo', 'Post Proper Northside', 'Post Proper Southside', 'Rizal', 'San Antonio', 'San Isidro', 'San Lorenzo', 'Santa Cruz', 'Singkamas', 'South Cembo', 'Tejeros', 'Urdaneta', 'Valenzuela', 'West Rembo'],
                        'Malabon City': ['Acacia', 'Baritan', 'Bayan-Bayanan', 'Catmon', 'Concepcion', 'C-3', 'Dampalit', 'Flores', 'Hulong Duhat', 'Ibaba', 'Longos', 'Maysilo', 'Muzon', 'Niugan', 'Panghulo', 'Potrero', 'San Agustin', 'Santolan', 'Tinajeros', 'Tonsuya', 'Tugatog', 'Tanza', 'Navotas East'],
                        'Mandaluyong City': ['Addition Hills', 'Bagong Silang', 'Barangka Drive', 'Barangka Ibaba', 'Barangka Ilaya', 'Barangka Itaas', 'Burol', 'Daang Bakal', 'Harapin Ang Bukas', 'Highway Hills', 'Hulo', 'Kalentong', 'Kansilayan', 'Mauway', 'Namayan', 'Old Zaniga', 'Pag-asa', 'Plainview', 'Poblacion', 'San Jose', 'Santa Ana', 'Vergara', 'Wack-Wack Greenhills', 'Wawang Pulo', 'Wack Wack', 'Barangka', 'Mandaluyong', 'Hulo'],
                        'Manila': Array.from({ length: 896 }, (_, i) => `Barangay ${i + 1}`),
                        'Marikina City': ['A. Bonifacio', 'Bayan-Bayanan', 'Calumpang', 'Concepcion Dos', 'Concepcion Uno', 'Fortune', 'Industrial Valley', 'Jesus Dela Peña', 'Malanday', 'Marikina Heights', 'Nangka', 'Parang', 'Poblacion', 'Santo Niño', 'Tumana', 'Valentine'],
                        'Muntinlupa City': ['Bayanan', 'Baywalk', 'Cupang', 'Poblacion', 'Putatan', 'Sucat', 'Sun Valley', 'Tunasan', 'New Alabang Village'],
                        'Navotas City': ['Bagumbayan North', 'Bagumbayan South', 'Bangculasi', 'Central', 'Daanghari', 'East Grace Park', 'Manuel A. Roxas', 'NBBS Dagat-Dagatan', 'NBBS Proper', 'Navotas West', 'San Jose', 'San Rafael Village', 'Sipac', 'Tangos North', 'Tangos South', 'Tanza'],
                        'Parañaque City': ['Baclaran', 'B.F. Homes', 'Don Bosco', 'La Huerta', 'Merville', 'Moonwalk', 'P. Dela Cruz', 'San Antonio', 'San Dionisio', 'San Isidro', 'San Martin de Porres', 'Santo Niño', 'Sun Valley', 'Tambo', 'Vitug', 'Zapotal'],
                        'Pasay City': Array.from({ length: 201 }, (_, i) => `Barangay ${i + 1}`),
                        'Pasig City': ['Bagong Ilog', 'Bagong Katipunan', 'Bambang', 'Buting', 'Caniogan', 'Dela Paz', 'Kalawaan', 'Kapasigan', 'Malinao', 'Manggahan', 'Maybunga', 'Oranbo', 'Pineda', 'Pinagbuhatan', 'Rosario', 'Sampaloc', 'San Antonio', 'San Joaquin', 'San Miguel', 'San Nicolas', 'Santolan', 'Sta. Lucia', 'Sta. Rosa', 'Sumilang', 'Ugong'],
                        'Pateros': ['Aguho', 'Bautista', 'C.M. Recto', 'Magtanggol', 'Poblacion', 'San Roque', 'Tandang Sora', 'Valenzuela'],
                        'Quezon City': ['Alicia', 'Amihan', 'Bagbag', 'Bahay Toro', 'Balingasa', 'Bayanihan', 'Bungad', 'Camp Aguinaldo', 'Central', 'Commonwealth', 'Culiat', 'Damar', 'Diliman', 'Don Manuel', 'Doña Imelda', 'Fairview', 'Greater Lagro', 'Gulod', 'Holy Spirit', 'Kaligayahan', 'Kamuning', 'Katipunan', 'Laging Handa', 'Libis', 'Malanday', 'Mangga', 'Mariblo', 'Masagana', 'N. S. Amoranto', 'New Era', 'New Manila', 'Payatas', 'Project 6', 'Project 7', 'Quirino 2-A', 'Roxas', 'Sangandaan', 'San Martin de Porres', 'Santa Cruz', 'Tandang Sora', 'U.P. Campus', 'Vasra', 'West Triangle', 'White Plains'],
                        'San Juan City': ['Arenal', 'Batis', 'Corazon de Jesus', 'Ermitaño', 'Greenhills', 'Isabelita', 'Maytunas', 'Onse', 'Pedro Cruz', 'Poblacion', 'Salapan', 'San Perfecto', 'Santa Lucia', 'Tibagan', 'West Crame', 'Balong-Bato', 'Bantayan', 'H. Bautista', 'Kagitingan', 'Little Baguio', 'Paseo de Roxas', 'Rivera', 'Talatak', 'Tinajeros'],
                        'Taguig City': ['Bagong Tanyag', 'Bambang', 'Calzada', 'Central Bicutan', 'Cembo', 'Comembo', 'Hagonoy', 'Ibayo-Tipas', 'Katuparan', 'Lower Bicutan', 'Maharlika Village', 'Napindan', 'New Lower Bicutan', 'North Daang Hari', 'Palingon', 'Pembo', 'Pinagsama', 'Santa Ana', 'South Daang Hari', 'Tanyag', 'Tuktukan', 'Upper Bicutan', 'Wawa', 'Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7', 'Zone 8', 'Zone 9', 'Zone 10', 'Zone 11', 'Zone 12'],
                        'Valenzuela City': ['Arkong Bato', 'Bignay', 'Canumay East', 'Canumay West', 'Coloong', 'Dalandanan', 'Gen. T. de Leon', 'Isla', 'Karuhatan', 'Lawang Bato', 'Libis', 'Mabolo', 'Malanday', 'Mapulang Lupa', 'Marulas', 'Poblacion', 'Polo', 'Rincon', 'Tagalag', 'Tugatog', 'Ugong', 'Veinte Reales', 'Wawang Pulo'],
                    },
                    'Cebu': {
                        'Cebu City': ['Adlaon', 'Apas', 'Bacayan', 'Lahug', 'Mambaling', 'Parang', 'Poblacion', 'San Antonio', 'Sambag', 'Talamban'],
                    },
                    'Davao del Sur': {
                        'Davao City': ['Bajada', 'Buhangin', 'Poblacion', 'Talomo', 'Tugbok', 'Ulas', 'San Pedro', 'Sasa', 'Marapangi', 'Toril'],
                    }
                };

                const normalizeText = (value = '') => String(value).toLowerCase().replace(/[^a-z0-9\s]/gi, '').replace(/\s+/g, ' ').trim();

                const buildAddressMap = (provinces = []) => {
                    const addressMap = {};

                    provinces.forEach((province) => {
                        const provinceName = province?.name;
                        if (!provinceName) {
                            return;
                        }

                        addressMap[provinceName] = {};
                        (province.cities || []).forEach((city) => {
                            const cityName = city?.name;
                            if (!cityName) {
                                return;
                            }

                            addressMap[provinceName][cityName] = city.barangays || [];
                        });
                    });

                    return addressMap;
                };

                const loadPhilippineAddressData = async () => {
                    try {
                        const response = await fetch('/api/v1/address-data/philippines', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Unable to fetch address data');
                        }

                        const payload = await response.json();
                        const provinces = payload?.data?.provinces || [];

                        return buildAddressMap(provinces);
                    } catch (error) {
                        console.warn('Falling back to local Philippine address dataset.', error);
                        return fallbackPhilippineAddressData;
                    }
                };

                const populateDatalist = (datalist, values = []) => {
                    if (!datalist) return;
                    datalist.innerHTML = '';
                    values.forEach((value) => {
                        const option = document.createElement('option');
                        option.value = value;
                        datalist.appendChild(option);
                    });
                };

                const initAddressGroup = async (root) => {
                    const provinceInput = root.querySelector('[data-address-field="province"]');
                    const cityInput = root.querySelector('[data-address-field="city"]');
                    const barangayInput = root.querySelector('[data-address-field="barangay"]');

                    if (!provinceInput || !cityInput || !barangayInput) {
                        return;
                    }

                    const philippineAddressData = await loadPhilippineAddressData();
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
                roots.forEach((root) => {
                    initAddressGroup(root);
                });
            })();
        </script>
    @endpush
@endonce
