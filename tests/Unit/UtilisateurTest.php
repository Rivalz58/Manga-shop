<?php

namespace App\Tests\Unit;

use App\Document\Utilisateur;
use PHPUnit\Framework\TestCase;

class UtilisateurTest extends TestCase
{
    // TEST 21 : Rôles par défaut incluent ROLE_USER
    public function testRolesParDefaut(): void
    {
        $user = new Utilisateur();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    // TEST 22 : isAdmin() retourne false par défaut
    public function testIsAdminFalseParDefaut(): void
    {
        $user = new Utilisateur();
        $this->assertFalse($user->isAdmin());
    }

    // TEST 23 : isAdmin() retourne true si ROLE_ADMIN
    public function testIsAdminTrue(): void
    {
        $user = new Utilisateur();
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertTrue($user->isAdmin());
    }

    // TEST 24 : getUserIdentifier() retourne l'email
    public function testGetUserIdentifier(): void
    {
        $user = new Utilisateur();
        $user->setEmail('test@pokemon.fr');
        $this->assertSame('test@pokemon.fr', $user->getUserIdentifier());
    }

    // TEST 25 : getNomComplet() concatène prénom + nom
    public function testGetNomComplet(): void
    {
        $user = new Utilisateur();
        $user->setPrenom('Sacha')->setNom('Ketchum');
        $this->assertSame('Sacha Ketchum', $user->getNomComplet());
    }

    // TEST 26 : Compte actif par défaut
    public function testActifParDefaut(): void
    {
        $user = new Utilisateur();
        $this->assertTrue($user->isActif());
    }

    // TEST 27 : eraseCredentials() ne lève pas d'exception
    public function testEraseCredentials(): void
    {
        $user = new Utilisateur();
        $user->eraseCredentials();
        $this->assertTrue(true); // Pas d'exception = succès
    }
}
