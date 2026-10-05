<?php
require_once 'includes/functions.php';
include 'includes/header.php';

// Array of 100 high-quality luxury hotel gallery images
$galleryImages = [
    // Base & Existing Images
    ['url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80', 'title' => 'Hotel Exterior & Grounds'],
    ['url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80', 'title' => 'Luxury Lounge & Foyer'],
    ['url' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=600&q=80', 'title' => 'Infinity Oceanfront Pool'],
    ['url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80', 'title' => 'Spa & Wellness Sanctuary'],
    ['url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=600&q=80', 'title' => 'Signature Fine Dining'],
    ['url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=600&q=80', 'title' => 'Private Beach Cove'],
    ['url' => 'https://images.unsplash.com/photo-1553240799-36bbf332a5c3?auto=format&fit=crop&w=600&q=80', 'title' => 'Gourmet Seafood Platter'],
    ['url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80', 'title' => 'Prime Culinary Cuts'],
    ['url' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=600&q=80', 'title' => 'Fresh Catch Dining'],
    ['url' => 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?auto=format&fit=crop&w=600&q=80', 'title' => 'Artisanal Pasta Delight'],

    // Architecture & Resort Grounds
    ['url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=600&q=80', 'title' => 'Sunset Architecture'],
    ['url' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=600&q=80', 'title' => 'Resort Lagoon View'],
    ['url' => 'https://images.unsplash.com/photo-1561501900-3701fa6a0864?auto=format&fit=crop&w=600&q=80', 'title' => 'Evening Courtyard Lights'],
    ['url' => 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?auto=format&fit=crop&w=600&q=80', 'title' => 'Tropical Gardens'],
    ['url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80', 'title' => 'Executive Grand Lobby'],
    ['url' => 'https://images.unsplash.com/photo-1563911302283-d2bc129e7570?auto=format&fit=crop&w=600&q=80', 'title' => 'Coastal Promenade'],
    ['url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=600&q=80', 'title' => 'Pristine Villa Exterior'],
    ['url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80', 'title' => 'Sunset Palm Pathway'],
    ['url' => 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=600&q=80', 'title' => 'Acoustic Marble Atrium'],
    ['url' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=600&q=80', 'title' => 'Beachside Bungalows'],

    // Accommodations & Suites
    ['url' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=600&q=80', 'title' => 'Ocean View Presidential Suite'],
    ['url' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80', 'title' => 'Master Deluxe King Suite'],
    ['url' => 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=600&q=80', 'title' => 'Modern Minimalist Room'],
    ['url' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=600&q=80', 'title' => 'Private Balcony Sunset'],
    ['url' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80', 'title' => 'Cozy Luxury Bedroom'],
    ['url' => 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=600&q=80', 'title' => 'Ensuite Marble Bathroom'],
    ['url' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=600&q=80', 'title' => 'Sunlit Resort Penthouse'],
    ['url' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=600&q=80', 'title' => 'Executive Bed View'],
    ['url' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=600&q=80', 'title' => 'Designer Suite Lounge'],
    ['url' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?auto=format&fit=crop&w=600&q=80', 'title' => 'Overwater Bungalow Room'],

    // Gastronomy & Drinks
    ['url' => 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?auto=format&fit=crop&w=600&q=80', 'title' => 'Coastal Paella Banquet'],
    ['url' => 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?auto=format&fit=crop&w=600&q=80', 'title' => 'Calimari & Dip Starters'],
    ['url' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=600&q=80', 'title' => 'Charcuterie Selection'],
    ['url' => 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?auto=format&fit=crop&w=600&q=80', 'title' => 'Gourmet Artisanal Bruschetta'],
    ['url' => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?auto=format&fit=crop&w=600&q=80', 'title' => 'Full Ocean Breakfast'],
    ['url' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=600&q=80', 'title' => 'Avocado Poached Toast'],
    ['url' => 'https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=600&q=80', 'title' => 'Belgian Berry Waffles'],
    ['url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=600&q=80', 'title' => 'Valrhona Lava Cake'],
    ['url' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=600&q=80', 'title' => 'Classic Italian Tiramisu'],
    ['url' => 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?auto=format&fit=crop&w=600&q=80', 'title' => 'Tropical Mango Smoothie'],
    ['url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80', 'title' => 'Azure Ocean Cocktail'],
    ['url' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=600&q=80', 'title' => 'Reserve Chardonnay'],
    ['url' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=600&q=80', 'title' => 'Artisan Cold Brew'],
    ['url' => 'https://images.unsplash.com/photo-1525385133512-2f3bdd039054?auto=format&fit=crop&w=600&q=80', 'title' => 'Fresh Young Coconut'],
    ['url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80', 'title' => 'Wagyu Beef Burger'],
    ['url' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=600&q=80', 'title' => 'Chilled Pacific Oysters'],
    ['url' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=80', 'title' => 'Coconut Mango Panna Cotta'],
    ['url' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?auto=format&fit=crop&w=600&q=80', 'title' => 'Seared Ahi Tuna Tartare'],
    ['url' => 'https://images.unsplash.com/photo-1608039829572-78524f79c4c7?auto=format&fit=crop&w=600&q=80', 'title' => 'Smoked Salmon Benedict'],
    ['url' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=600&q=80', 'title' => 'Sparkling Berry Sangria'],

    // Pools, Beach & Leisure
    ['url' => 'https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?auto=format&fit=crop&w=600&q=80', 'title' => 'Resort Main Swimming Pool'],
    ['url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80', 'title' => 'Private Sandy Shores'],
    ['url' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=600&q=80', 'title' => 'Cabana Beach Loungers'],
    ['url' => 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=600&q=80', 'title' => 'Coastal Sunset Horizon'],
    ['url' => 'https://images.unsplash.com/photo-1583037189850-1921ae7c6c34?auto=format&fit=crop&w=600&q=80', 'title' => 'Poolside Cocktail Bar'],
    ['url' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=600&q=80', 'title' => 'Hydrotherapy Spa Pool'],
    ['url' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=600&q=80', 'title' => 'Relaxation Sauna & Steam'],
    ['url' => 'https://images.unsplash.com/photo-1530521954074-e64f6810b32d?auto=format&fit=crop&w=600&q=80', 'title' => 'Sailing & Yacht Services'],
    ['url' => 'https://images.unsplash.com/photo-1502680390469-be75c86b636f?auto=format&fit=crop&w=600&q=80', 'title' => 'Ocean Kayak Adventures'],
    ['url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=600&q=80', 'title' => 'Scuba Diving Excursions'],

    // Events, Hospitality & Life
    ['url' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=600&q=80', 'title' => 'Grand Oceanfront Ballroom'],
    ['url' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=600&q=80', 'title' => 'Sunset Beach Weddings'],
    ['url' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=600&q=80', 'title' => 'Gala Dinner Setup'],
    ['url' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=600&q=80', 'title' => 'Outdoor Celebration Lawn'],
    ['url' => 'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=600&q=80', 'title' => 'Evening Lounge Ambience'],
    ['url' => 'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=600&q=80', 'title' => 'Intimate Dining Table'],
    ['url' => 'https://images.unsplash.com/photo-1578474846511-04ba529f0b88?auto=format&fit=crop&w=600&q=80', 'title' => 'Chef Kitchen Service'],
    ['url' => 'https://images.unsplash.com/photo-1517840901100-8179e982acb7?auto=format&fit=crop&w=600&q=80', 'title' => 'Concierge Guest Lounge'],
    ['url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80', 'title' => 'Palm Pavilion Walkways'],
    ['url' => 'https://images.unsplash.com/photo-1501117716987-c8c394bb29df?auto=format&fit=crop&w=600&q=80', 'title' => 'Luxury Private Transfer'],

    // Additional Scenery & Decor
    ['url' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=600&q=80', 'title' => 'Tropical Paradise Path'],
    ['url' => 'https://images.unsplash.com/photo-1512915922686-57c11dde9b6b?auto=format&fit=crop&w=600&q=80', 'title' => 'Sunset Beach Fire Pit'],
    ['url' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=600&q=80', 'title' => 'Ocean Deck Recliners'],
    ['url' => 'https://images.unsplash.com/photo-1560624052-449f5ddf0c31?auto=format&fit=crop&w=600&q=80', 'title' => 'Fresh Flower Arrangements'],
    ['url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=600&q=80', 'title' => 'Modern Coastal Villa'],
    ['url' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=600&q=80', 'title' => 'Illuminated Pool Deck'],
    ['url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80', 'title' => 'Plush Lounge Seating'],
    ['url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80', 'title' => 'Marble Reception Desk'],
    ['url' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80', 'title' => 'Premium Bedding Decor'],
    ['url' => 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=600&q=80', 'title' => 'Contemporary Room Details'],

    ['url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80', 'title' => 'Morning Horizon View'],
    ['url' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=600&q=80', 'title' => 'Coastal Sun Lounger'],
    ['url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=600&q=80', 'title' => 'Romantic Candlelight Dinner'],
    ['url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80', 'title' => 'Aromatherapy Treatment Room'],
    ['url' => 'https://images.unsplash.com/photo-1553240799-36bbf332a5c3?auto=format&fit=crop&w=600&q=80', 'title' => 'Grilled Lobster Feast'],
    ['url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80', 'title' => 'Prime Angus Steak'],
    ['url' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=600&q=80', 'title' => 'Pan-Seared Salmon'],
    ['url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80', 'title' => 'Handcrafted Coastal Cocktails'],
    ['url' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=600&q=80', 'title' => 'Morning Espresso Bar'],
    ['url' => 'https://images.unsplash.com/photo-1525385133512-2f3bdd039054?auto=format&fit=crop&w=600&q=80', 'title' => 'Cold Coconut Refreshments'],

    ['url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80', 'title' => 'Gourmet House Burger'],
    ['url' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=600&q=80', 'title' => 'Fresh Oyster Bar'],
    ['url' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=80', 'title' => 'Tropical Dessert Platter'],
    ['url' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?auto=format&fit=crop&w=600&q=80', 'title' => 'Seared Tuna Appetizer'],
    ['url' => 'https://images.unsplash.com/photo-1608039829572-78524f79c4c7?auto=format&fit=crop&w=600&q=80', 'title' => 'Morning Eggs Benedict'],
    ['url' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=600&q=80', 'title' => 'House Sangria Pitcher'],
    ['url' => 'https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?auto=format&fit=crop&w=600&q=80', 'title' => 'Crystal Swimming Pool'],
    ['url' => 'https://images.unsplash.com/photo-1583037189850-1921ae7c6c34?auto=format&fit=crop&w=600&q=80', 'title' => 'Resort Evening Bar'],
    ['url' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=600&q=80', 'title' => 'Spa Hydro Massage'],
    ['url' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=600&q=80', 'title' => 'Relaxing Steam Sauna'],

    ['url' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=600&q=80', 'title' => 'Banquet & Event Space'],
    ['url' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=600&q=80', 'title' => 'Coastal Event Setups'],
    ['url' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=600&q=80', 'title' => 'Gala & Dinner Seating'],
    ['url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=600&q=80', 'title' => 'Private Beach View'],
    ['url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=600&q=80', 'title' => 'Dusk Architectural View'],
    ['url' => 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?auto=format&fit=crop&w=600&q=80', 'title' => 'Resort Palm Walkway'],
    ['url' => 'https://images.unsplash.com/photo-1563911302283-d2bc129e7570?auto=format&fit=crop&w=600&q=80', 'title' => 'Ocean Deck Lounge'],
    ['url' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=600&q=80', 'title' => 'Luxury Ocean Room'],
    ['url' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80', 'title' => 'Presidential Suite Bedroom'],
    ['url' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=600&q=80', 'title' => 'Balcony Ocean Seating'],

    ['url' => 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=600&q=80', 'title' => 'Marble Guest Bathroom'],
    ['url' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=600&q=80', 'title' => 'Penthouse Interior'],
    ['url' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=600&q=80', 'title' => 'Deluxe Suite Bedding'],
    ['url' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=600&q=80', 'title' => 'Luxury Living Lounge'],
    ['url' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?auto=format&fit=crop&w=600&q=80', 'title' => 'Overwater Haven Suite'],
    ['url' => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?auto=format&fit=crop&w=600&q=80', 'title' => 'Morning Buffet Spread'],
    ['url' => 'https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=600&q=80', 'title' => 'Fresh Berry Waffle Breakfast'],
    ['url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=600&q=80', 'title' => 'Decadent Lava Cake'],
    ['url' => 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?auto=format&fit=crop&w=600&q=80', 'title' => 'Chilled Tropical Smoothie'],
    ['url' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=600&q=80', 'title' => 'White Wine Glass Selection'],
    
    ['url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80','title'=> 'Resort Poolside Architecture'], 
    ['url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80','title' => 'Oceanview Suite Interior'],
    ['url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80','title' => 'Luxury Hydrotherapy Spa'],
    ['url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80','title' => 'Fine Dining Restaurant'],
    ['url' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80','title' => 'Grand Event Space'],
    ['url' => 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=1200&q=80','title' => 'Executive Meeting Suite']
];
?>

<style>
/* Section Container for Haven Experience */
.experience-container {
    display: flex;
    gap: 2.5rem;
    align-items: stretch;
    margin-bottom: 4rem;
}

.experience-image-col {
    flex: 1;
    min-width: 300px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.experience-image-col img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.experience-text-col {
    flex: 1.2;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

@media (max-width: 768px) {
    .experience-container {
        flex-direction: column;
    }
    .experience-image-col {
        height: 350px;
    }
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.25rem;
  margin-bottom: 4rem;
}

.gallery-item {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: var(--shadow);
  background-color: var(--navy);
  cursor: pointer;
  aspect-ratio: 4/3;
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease, opacity 0.4s ease;
}

.gallery-item:hover img {
  transform: scale(1.08);
  opacity: 0.85;
}

.gallery-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 0.75rem 1rem;
  background: linear-gradient(transparent, rgba(10, 25, 47, 0.9));
  color: var(--white);
  font-size: 0.85rem;
  font-weight: 500;
  transform: translateY(100%);
  transition: transform 0.3s ease;
}

.gallery-item:hover .gallery-caption {
  transform: translateY(0);
}

/* Lightbox Modal */
.lightbox-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(10, 25, 47, 0.95);
  z-index: 3000;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

.lightbox-modal.active {
  display: flex;
}

.lightbox-img {
  max-width: 90vw;
  max-height: 80vh;
  border-radius: 6px;
  box-shadow: var(--shadow);
  object-fit: contain;
}

.lightbox-title {
  color: var(--gold);
  margin-top: 1rem;
  font-size: 1.25rem;
  font-family: 'Playfair Display', serif;
}

.lightbox-close {
  position: absolute;
  top: 1.5rem;
  right: 2rem;
  color: var(--white);
  font-size: 2.5rem;
  cursor: pointer;
  line-height: 1;
}
</style>

<div class="container" style="margin-top:5rem;">
    <!-- Hotel Story Section -->
    <div class="experience-container">
        <!-- Hotel Overview Building Image Left -->
        <div class="experience-image-col">
            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80" alt="Azure Haven Hotel Overview Building">
        </div>

        <!-- Experience Text Right Spanning Vertical Height -->
        <div class="experience-text-col">
            <h1 style="color:var(--navy); font-size:2.8rem; margin-bottom:1rem;">The Azure Haven Experience</h1>
            
            <p style="color:var(--text-muted); font-size:1.05rem; line-height:1.8; margin-bottom:1rem; text-align:justify;">
                Founded along pristine oceanfront shores, Azure Haven Hotel operates as a sanctuary of coastal luxury and refined hospitality. Our resort seamlessly integrates sustainable architecture with world-class amenities, offering guests an immersive escape where personalized service meets effortless coastal living. From round-the-clock private concierge attendance to tailored wellness programs, every touchpoint of our operations is engineered to deliver an uninterrupted state of relaxation and prestige.
            </p>

            <p style="color:var(--text-muted); font-size:1.05rem; line-height:1.8; margin-bottom:1rem; text-align:justify;">
                At the core of our operations is a commitment to seamless, digitally integrated guest services. Guests can effortlessly request room delivery for gourmet meals, schedule private spa sanctuaries, or coordinate bespoke ocean excursions directly from their suites or restaurant tables. Our dedicated culinary team curates menus using daily catches from local sustainable fisheries, while our housekeeping and hospitality staff uphold rigorous standards to ensure every suite reflects timeless elegance and flawless comfort.
            </p>

            <p style="color:var(--text-muted); font-size:1.05rem; line-height:1.8; margin:0; text-align:justify;">
                Whether hosting private sunset celebrations, facilitating executive retreats, or offering an intimate haven for romantic escapes, Azure Haven Hotel harmonizes local coastal culture with internationally acclaimed resort standards. We continuously innovate our offerings—from our state-of-the-art infinity pools to our farm-to-table dining—ensuring every stay becomes an unforgettable chapter in our guests' luxury journeys.
            </p>
        </div>
    </div>

    <!-- Gallery Section (100 Images) -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
        <h2 style="color:var(--navy); font-size:2rem; margin:0;">Resort Gallery</h2>
        <span style="color:var(--gold); font-weight:600; font-size:1.05rem;"><?php echo count($galleryImages); ?> High-Resolution Photos</span>
    </div>

    <div class="gallery-grid">
        <?php foreach ($galleryImages as $index => $img): ?>
            <div class="gallery-item" onclick="openLightbox('<?php echo $img['url']; ?>', '<?php echo sanitize(addslashes($img['title'])); ?>')">
                <img src="<?php echo $img['url']; ?>" alt="<?php echo sanitize($img['title']); ?>" loading="lazy">
                <div class="gallery-caption"><?php echo sanitize($img['title']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Interactive Lightbox Viewer -->
<div id="galleryLightbox" class="lightbox-modal" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <img id="lightboxImg" class="lightbox-img" src="" alt="Enlarged View">
    <div id="lightboxTitle" class="lightbox-title"></div>
</div>

<script>
function openLightbox(url, title) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('lightboxTitle').innerText = title;
    document.getElementById('galleryLightbox').classList.add('active');
}

function closeLightbox() {
    document.getElementById('galleryLightbox').classList.remove('active');
}
</script>

<?php include 'includes/footer.php'; ?>