<?php
// Direct access protection
if (!defined('IS_PROFILE_PAGE')) {
    die('Bu sayfaya doğrudan erişim yasaktır.');
}
?>
<form action="profile.php" method="POST">
    <!-- Kişisel Bilgiler Kartı -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Kişisel Bilgiler</h3>
            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="first_name" class="block text-sm font-medium text-gray-700">Ad</label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user_profile['first_name'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                <div class="sm:col-span-3">
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Soyad</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user_profile['last_name'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                <div class="sm:col-span-3">
                    <label for="gender" class="block text-sm font-medium text-gray-700">Cinsiyet</label>
                    <select id="gender" name="gender" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                        <option value="" <?php echo !isset($user_profile['gender']) ? 'selected' : ''; ?>>Seçiniz</option>
                        <option value="Kadın" <?php echo ($user_profile['gender'] ?? '') === 'Kadın' ? 'selected' : ''; ?>>Kadın</option>
                        <option value="Erkek" <?php echo ($user_profile['gender'] ?? '') === 'Erkek' ? 'selected' : ''; ?>>Erkek</option>
                        <option value="Belirtmek İstemiyorum" <?php echo ($user_profile['gender'] ?? '') === 'Belirtmek İstemiyorum' ? 'selected' : ''; ?>>Belirtmek İstemiyorum</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- İletişim Bilgileri Kartı -->
    <div class="bg-white shadow sm:rounded-lg mt-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">İletişim Bilgileri</h3>
            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                    <p class="mt-2 text-xs text-gray-500">E-posta adresinizi değiştirirseniz, yeni adresinize gönderilen linki onaylamanız gerekecektir.</p>
                </div>
                <div class="sm:col-span-4">
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Telefon Numarası</label>
                    <input type="tel" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($user_profile['phone_number'] ?? ''); ?>" class="mt-1 block w-full">
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end mt-6">
        <button type="submit" class="bg-primary border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            Bilgileri Güncelle
        </button>
    </div>
</form>