<!-- Form Actions -->
<div class="flex flex-col sm:flex-row gap-4 pt-6">
    <button type="submit" 
            name="save_and_return"
            class="flex-1 sm:flex-none sm:min-w-[200px] bg-primary-600 text-white font-semibold py-3 px-8 rounded-xl hover:bg-primary-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
        <i class="fas fa-save mr-2"></i>
        Kaydet ve Listeye Dön
    </button>
    
    <button type="submit" 
            name="save_and_continue"
            class="flex-1 sm:flex-none sm:min-w-[200px] bg-green-600 text-white font-semibold py-3 px-8 rounded-xl hover:bg-green-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
        <i class="fas fa-check mr-2"></i>
        Kaydet ve Düzenlemeye Devam Et
    </button>
    
    <a href="products.php" 
       class="flex-1 sm:flex-none sm:min-w-[150px] bg-gray-100 text-gray-700 font-semibold py-3 px-8 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center">
        <i class="fas fa-times mr-2"></i>
        İptal
    </a>
</div>