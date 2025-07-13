<?php
require_once 'services/AuthService.php';
$authService = new AuthService();

require_once 'services/AboutService.php';
$aboutService = new AboutService();
$content = $aboutService->getAboutPageContent();

$settings = $content['settings'];
$values = $content['values'];
$team = $content['team'];

include 'includes/header.php';
?>

<section class="relative bg-gradient-to-r from-primary to-purple-600 text-white py-16 overflow-hidden">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80'); opacity: 0.3;">
    </div>
    <div class="relative max-w-7xl mx-auto px-5 text-center">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4">Hakkımızda</h1>
        <p class="text-xl text-white/90">Kaliteli ayakkabı ve mükemmel hizmet anlayışımızla tanışın</p>
    </div>
</section>

<section class="py-3 bg-white">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-10">
        </div>

        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1">
                <img src="<?php echo htmlspecialchars($settings['story_image_url'] ?? ''); ?>" alt="Mağazamız"
                    class="w-full md:w-2/3 mx-auto rounded-lg shadow-lg">
            </div>
            <div class="flex-1 text-center lg:text-left">
                <h3 class="text-2xl font-bold mb-5 text-secondary">
                    <?php echo htmlspecialchars($settings['story_content_title'] ?? ''); ?>
                </h3>
                <p class="mb-5 text-gray-600 leading-relaxed">
                    <?php echo htmlspecialchars($settings['story_content_p1'] ?? ''); ?>
                </p>
                <p class="mb-5 text-gray-600 leading-relaxed">
                    <?php echo htmlspecialchars($settings['story_content_p2'] ?? ''); ?>
                </p>
                <p class="text-gray-600 leading-relaxed">
                    <?php echo htmlspecialchars($settings['story_content_p3'] ?? ''); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-12 bg-light">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-bold mb-3 text-secondary">
                <?php echo htmlspecialchars($settings['values_title'] ?? ''); ?>
            </h2>
            <p class="text-gray-600"><?php echo htmlspecialchars($settings['values_subtitle'] ?? ''); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($values as $value): ?>
                <div
                    class="bg-white p-8 rounded-lg shadow-lg text-center hover:-translate-y-2 transition-transform duration-300">
                    <div
                        class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
                        <i class="<?php echo htmlspecialchars($value['icon'] ?? ''); ?>"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4 text-secondary"><?php echo htmlspecialchars($value['title'] ?? ''); ?>
                    </h3>
                    <p class="text-gray-600 leading-relaxed"><?php echo htmlspecialchars($value['content'] ?? ''); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-bold mb-3 text-secondary">
                <?php echo htmlspecialchars($settings['team_title'] ?? ''); ?>
            </h2>
            <p class="text-gray-600"><?php echo htmlspecialchars($settings['team_subtitle'] ?? ''); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($team as $member): ?>
                <div
                    class="bg-light p-6 rounded-lg shadow-lg text-center hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-24 h-24 mx-auto mb-4 overflow-hidden rounded-full">
                        <img src="<?php echo htmlspecialchars($member['image_url'] ?? ''); ?>"
                            alt="<?php echo htmlspecialchars($member['title'] ?? ''); ?>"
                            class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-lg font-bold mb-1 text-secondary">
                        <?php echo htmlspecialchars($member['title'] ?? ''); ?>
                    </h3>
                    <p class="text-primary font-semibold mb-3"><?php echo htmlspecialchars($member['subtitle'] ?? ''); ?>
                    </p>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        <?php echo htmlspecialchars($member['content'] ?? ''); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>