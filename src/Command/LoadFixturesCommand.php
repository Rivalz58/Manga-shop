<?php

namespace App\Command;

use App\Document\Carte;
use App\Document\Commande;
use App\Document\LigneCommande;
use App\Document\Serie;
use App\Document\Utilisateur;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:load-fixtures', description: 'Charge les données de test MongoDB (minimum 50 documents)')]
class LoadFixturesCommand extends Command
{
    public function __construct(
        private DocumentManager $dm,
        private UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Vider la base avant de charger');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Chargement des fixtures PokéShop');

        if ($input->getOption('reset')) {
            $this->dm->getDocumentCollection(Carte::class)->drop();
            $this->dm->getDocumentCollection(Serie::class)->drop();
            $this->dm->getDocumentCollection(Utilisateur::class)->drop();
            $this->dm->getDocumentCollection(Commande::class)->drop();
            $io->warning('Base vidée.');
        }

        // Séries
        $seriesData = [
            ['Écarlate et Violet', 2023, 'Série principale de la 9ème génération de jeux Pokémon.'],
            ['Obsidienne de Feu', 2023, 'Extension de la série Écarlate et Violet avec des cartes inédites.'],
            ['Paldéa Évolué', 2023, 'Évolutions de la région de Paldéa.'],
            ['Mascarade Crépusculaire', 2024, 'Dernière extension avec des cartes rares.'],
            ['Base Set', 1999, 'La série originale de cartes Pokémon.'],
            ['Jungle', 1999, 'Deuxième extension officielle.'],
        ];

        $series = [];
        foreach ($seriesData as [$nom, $annee, $desc]) {
            $s = new Serie();
            $s->setNom($nom)->setAnnee($annee)->setDescription($desc);
            $this->dm->persist($s);
            $series[$nom] = $s;
        }
        $this->dm->flush();
        $io->success(count($seriesData) . ' séries créées.');

        // Cartes (>50 documents)
        $cartesData = [
            // [nom, serie, type, rarete, prix, stock, pv, pokedex]
            ['Pikachu', 'Écarlate et Violet', 'Électrique', 'Commune', 0.50, 25, 60, 25],
            ['Dracaufeu', 'Obsidienne de Feu', 'Feu', 'Ultra Rare', 12.99, 5, 180, 6],
            ['Mewtwo', 'Mascarade Crépusculaire', 'Psychique', 'Secrète', 29.99, 3, 150, 150],
            ['Évoli', 'Paldéa Évolué', 'Normal', 'Peu commune', 1.20, 18, 55, 133],
            ['Aquali', 'Paldéa Évolué', 'Eau', 'Rare', 3.50, 10, 130, 134],
            ['Pyroli', 'Paldéa Évolué', 'Feu', 'Rare', 3.80, 8, 130, 136],
            ['Voltali', 'Paldéa Évolué', 'Électrique', 'Rare', 3.60, 9, 130, 135],
            ['Florizarre', 'Écarlate et Violet', 'Plante', 'Rare', 4.00, 8, 160, 3],
            ['Tortank', 'Obsidienne de Feu', 'Eau', 'Rare', 4.20, 7, 180, 9],
            ['Ronflex', 'Paldéa Évolué', 'Normal', 'Peu commune', 1.50, 15, 160, 143],
            ['Lokhlass', 'Mascarade Crépusculaire', 'Eau', 'Rare', 5.00, 6, 130, 131],
            ['Ditto', 'Écarlate et Violet', 'Normal', 'Peu commune', 2.00, 12, 48, 132],
            ['Rayquaza', 'Obsidienne de Feu', 'Dragon', 'Secrète', 45.00, 2, 170, 384],
            ['Lucario', 'Paldéa Évolué', 'Combat', 'Ultra Rare', 8.90, 9, 120, 448],
            ['Gardevoir', 'Écarlate et Violet', 'Psychique', 'Ultra Rare', 9.50, 6, 120, 282],
            ['Gengar', 'Obsidienne de Feu', 'Psychique', 'Rare', 6.00, 11, 120, 94],
            ['Charizard EX', 'Mascarade Crépusculaire', 'Feu', 'Secrète', 89.99, 1, 230, 6],
            ['Pikachu VMAX', 'Paldéa Évolué', 'Électrique', 'Ultra Rare', 22.00, 4, 290, 25],
            ['Bulbizarre', 'Base Set', 'Plante', 'Commune', 0.30, 30, 45, 1],
            ['Salamèche', 'Base Set', 'Feu', 'Commune', 0.30, 28, 39, 4],
            ['Carapuce', 'Base Set', 'Eau', 'Commune', 0.35, 25, 44, 7],
            ['Mew', 'Mascarade Crépusculaire', 'Psychique', 'Ultra Rare', 18.00, 5, 60, 151],
            ['Lugia', 'Base Set', 'Incolore', 'Secrète', 35.00, 3, 110, 249],
            ['Ho-Oh', 'Jungle', 'Feu', 'Secrète', 28.00, 3, 120, 250],
            ['Suicune', 'Jungle', 'Eau', 'Rare', 7.50, 7, 100, 245],
            ['Raichu', 'Base Set', 'Électrique', 'Rare', 5.00, 10, 90, 26],
            ['Nidoking', 'Jungle', 'Combat', 'Rare', 4.50, 8, 110, 34],
            ['Nidoqueen', 'Jungle', 'Plante', 'Rare', 4.50, 8, 110, 31],
            ['Dracaufeu EX', 'Écarlate et Violet', 'Feu', 'Secrète', 75.00, 2, 250, 6],
            ['Pikachu Illustré', 'Base Set', 'Électrique', 'Secrète', 150.00, 1, 70, 25],
            ['Sylvali', 'Paldéa Évolué', 'Fée', 'Rare', 4.20, 9, 130, 700],
            ['Mentali', 'Paldéa Évolué', 'Psychique', 'Rare', 4.00, 10, 130, 196],
            ['Phyllali', 'Paldéa Évolué', 'Plante', 'Rare', 3.80, 8, 130, 470],
            ['Noctali', 'Paldéa Évolué', 'Obscurité', 'Rare', 4.10, 9, 130, 197],
            ['Givrali', 'Paldéa Évolué', 'Eau', 'Rare', 3.90, 10, 130, 471],
            ['Dracolosse', 'Base Set', 'Dragon', 'Rare', 8.00, 6, 180, 149],
            ['Artikodin', 'Jungle', 'Eau', 'Rare', 9.00, 5, 110, 144],
            ['Sulfura', 'Jungle', 'Feu', 'Rare', 9.00, 5, 110, 146],
            ['Électhor', 'Jungle', 'Électrique', 'Rare', 9.00, 5, 110, 145],
            ['Léviator', 'Base Set', 'Eau', 'Rare', 5.50, 8, 120, 130],
            ['Kangaskhan', 'Jungle', 'Normal', 'Rare', 3.50, 10, 105, 115],
            ['Absol', 'Obsidienne de Feu', 'Obscurité', 'Peu commune', 2.50, 14, 65, 359],
            ['Arcanin', 'Base Set', 'Feu', 'Rare', 5.00, 9, 120, 59],
            ['Mackogneur', 'Jungle', 'Combat', 'Commune', 0.40, 22, 80, 67],
            ['Papilusion', 'Jungle', 'Plante', 'Peu commune', 0.80, 16, 60, 12],
            ['Poliwhirl', 'Base Set', 'Eau', 'Peu commune', 0.70, 18, 65, 61],
            ['Rhinocorne', 'Jungle', 'Combat', 'Commune', 0.25, 35, 45, 111],
            ['Soporifik', 'Jungle', 'Psychique', 'Commune', 0.30, 28, 50, 96],
            ['Excelangue', 'Jungle', 'Normal', 'Commune', 0.35, 22, 65, 108],
            ['Mélodelfe', 'Écarlate et Violet', 'Fée', 'Commune', 0.40, 20, 60, 39],
            ['Togepi', 'Mascarade Crépusculaire', 'Fée', 'Peu commune', 1.00, 20, 35, 175],
            ['Noctowl', 'Mascarade Crépusculaire', 'Incolore', 'Peu commune', 0.90, 17, 100, 164],
            ['Umbreon', 'Obsidienne de Feu', 'Obscurité', 'Rare', 6.50, 8, 110, 197],
            ['Espeon', 'Obsidienne de Feu', 'Psychique', 'Rare', 6.00, 7, 110, 196],
            ['Snorlax', 'Mascarade Crépusculaire', 'Normal', 'Peu commune', 1.80, 13, 160, 143],
        ];

        $cartesCreees = [];
        foreach ($cartesData as [$nom, $serieNom, $type, $rarete, $prix, $stock, $pv, $pokedex]) {
            $c = new Carte();
            $c->setNom($nom)->setSerie($series[$serieNom] ?? null)
              ->setTypePokemon($type)->setRarete($rarete)
              ->setPrix($prix)->setStock($stock)->setPv($pv)->setNumeroPokedex($pokedex)
              ->setDescription("Carte $nom de type $type — série $serieNom.");
            $this->dm->persist($c);
            $cartesCreees[] = $c;
        }
        $this->dm->flush();
        $io->success(count($cartesData) . ' cartes créées.');

        // Utilisateurs
        $admin = new Utilisateur();
        $admin->setPrenom('Admin')->setNom('PokéShop')->setEmail('admin@pokeshop.fr')
              ->setPassword($this->hasher->hashPassword($admin, 'Admin1234!'))
              ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
              ->setAdresse('1 rue du Pokémon, 75001 Paris');
        $this->dm->persist($admin);

        $user = new Utilisateur();
        $user->setPrenom('Sacha')->setNom('Ketchum')->setEmail('sacha@pokemon.fr')
             ->setPassword($this->hasher->hashPassword($user, 'Sacha1234!'))
             ->setAdresse('Bourg Palette, 75002 Paris');
        $this->dm->persist($user);

        $user2 = new Utilisateur();
        $user2->setPrenom('Misty')->setNom('Cascade')->setEmail('misty@pokemon.fr')
              ->setPassword($this->hasher->hashPassword($user2, 'Misty1234!'))
              ->setAdresse('Cendarmord, 75003 Paris');
        $this->dm->persist($user2);

        $this->dm->flush();
        $io->success('3 utilisateurs créés (admin@pokeshop.fr / Admin1234!).');

        // Commandes de démonstration
        $pikachu = $cartesCreees[0];
        $dracaufeu = $cartesCreees[1];

        foreach (['Livrée', 'Confirmée', 'En attente'] as $statut) {
            $commande = new Commande();
            $commande->setClientNom('Sacha Ketchum')
                     ->setClientEmail('sacha@pokemon.fr')
                     ->setAdresseLivraison('Bourg Palette, 75002 Paris')
                     ->setUtilisateur($user)
                     ->setStatut($statut);

            $ligne = new LigneCommande();
            $ligne->setCarte($pikachu)->setQuantite(2)->setPrixUnitaire($pikachu->getPrix());
            $commande->addLigne($ligne);

            $ligne2 = new LigneCommande();
            $ligne2->setCarte($dracaufeu)->setQuantite(1)->setPrixUnitaire($dracaufeu->getPrix());
            $commande->addLigne($ligne2);

            $commande->calculerTotal();
            $this->dm->persist($commande);
        }

        $this->dm->flush();
        $io->success('3 commandes de démonstration créées.');

        $io->success('Fixtures chargées avec succès ! ' . (count($cartesData) + 6 + 3) . ' documents MongoDB créés.');

        return Command::SUCCESS;
    }
}
