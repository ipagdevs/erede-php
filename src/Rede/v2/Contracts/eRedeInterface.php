<?php

namespace Rede\v2\Contracts;

use Rede\Transaction;
use Rede\AbstractAuthentication;

interface eRedeInterface
{
    public function generateOAuthToken(): AbstractAuthentication;
    public function authorize(Transaction $transaction): Transaction;
    public function create(Transaction $transaction): Transaction;
    public function platform(string $platform, string $platformVersion): static;
    public function cancel(Transaction $transaction): Transaction;
    public function getById(string $tid): Transaction;
    public function get(string $tid): Transaction;
    public function getByReference(string $reference): Transaction;
    public function getRefunds(string $tid): Transaction;
    public function zero(Transaction $transaction): Transaction;
    public function capture(Transaction $transaction): Transaction;

}
