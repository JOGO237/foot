<!-- Footer avec Sponsors -->
    <footer class="bg-gray-800 dark:bg-gray-900 text-white mt-12">
        <div class="container mx-auto px-4 py-8">
            <!-- Section Sponsors -->
             
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-center mb-6 text-yellow-400">
                    <i class="fas fa-handshake mr-2"></i>Nos Partenaires
                </h3>
                <div class="grid grid-cols-3 md:grid-cols-3 gap-6">
                    <?php
                    require_once 'config/database.php';
                    $stmt = $pdo->query("SELECT * FROM sponsors WHERE statut = 'actif' ORDER BY type, nom");
                    $sponsors = $stmt->fetchAll();
                    
                    $groupedSponsors = [];
                    foreach($sponsors as $sponsor) {
                        $groupedSponsors[$sponsor['type']][] = $sponsor;
                    }
                    
                    foreach(['sponsor' => 'Sponsors', 'partenaire' => 'Partenaires', 'investisseur' => 'Investisseurs'] as $type => $title): 
                        if(isset($groupedSponsors[$type])):
                    ?>
                    <div class="text-center">
                        <h4 class="text-lg font-semibold mb-4 text-green-400"><?php echo $title; ?></h4>
                        <div class="space-y-3">
                            <?php foreach($groupedSponsors[$type] as $sponsor): ?>
                            <div class="bg-gray-700 dark:bg-gray-800 rounded-lg p-4 hover:bg-gray-600 transition-colors duration-200">
                                <?php if($sponsor['logo']): ?>
                                <img src="uploads/logos/<?php echo htmlspecialchars($sponsor['logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($sponsor['nom']); ?>" 
                                     class="w-16 h-16 mx-auto mb-2 object-contain">
                                <?php endif; ?>
                                <h5 class="font-medium text-sm"><?php echo htmlspecialchars($sponsor['nom']); ?></h5>
                                <?php if($sponsor['site_web']): ?>
                                <a href="<?php echo htmlspecialchars($sponsor['site_web']); ?>" 
                                   target="_blank" class="text-yellow-400 hover:text-yellow-300 text-xs">
                                    <i class="fas fa-external-link-alt"></i> Site web
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            
            <!-- Footer Principal -->
            <div class="border-t border-gray-600 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <h4 class="text-lg font-semibold mb-4 text-green-400">Football Jeunes Cameroun</h4>
                        <p class="text-gray-300 text-sm">
                            Plateforme officielle de gestion du football jeunes au Cameroun. 
                            Suivez vos équipes favorites et découvrez les talents de demain.
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-semibold mb-4 text-yellow-400">Navigation</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="index.php" class="text-gray-300 hover:text-white transition-colors">Accueil</a></li>
                            <li><a href="clubs.php" class="text-gray-300 hover:text-white transition-colors">Clubs</a></li>
                            <li><a href="joueurs.php" class="text-gray-300 hover:text-white transition-colors">Joueurs</a></li>
                            <li><a href="classements.php" class="text-gray-300 hover:text-white transition-colors">Classements</a></li>
                            
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-semibold mb-4 text-red-400">Compétitions</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="classements.php?categorie=1" class="text-gray-300 hover:text-white transition-colors">U-13</a></li>
                            <li><a href="classements.php?categorie=2" class="text-gray-300 hover:text-white transition-colors">U-15</a></li>
                            <li><a href="classements.php?categorie=3" class="text-gray-300 hover:text-white transition-colors">U-17</a></li>
                            <li><a href="classements.php?categorie=4" class="text-gray-300 hover:text-white transition-colors">U-19</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-semibold mb-4 text-green-400">Contact</h4>
                        <div class="space-y-2 text-sm text-gray-300">
                            <p><i class="fas fa-envelope mr-2"></i>contact@footballcameroun.com</p>
                            <p><i class="fas fa-phone mr-2"></i>+237 6XX XXX XXX</p>
                            <p><i class="fas fa-map-marker-alt mr-2"></i>Yaoundé, Cameroun</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="border-t border-gray-600 mt-6 pt-6 text-center">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">
                        © <?php echo date('Y'); ?> Football Jeunes Cameroun. Tous droits réservés.
                    </p>
                    <div class="flex space-x-4 mt-4 md:mt-0">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-green-500 rounded"></div>
                            <div class="w-4 h-4 bg-red-500 rounded"></div>
                            <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                            <span class="text-gray-400 text-sm ml-2">Fier d'être Camerounais</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>