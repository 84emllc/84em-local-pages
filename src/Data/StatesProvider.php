<?php
/**
 * US States Data Provider
 *
 * @package EightyFourEM\LocalPages\Data
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\Data;

use EightyFourEM\LocalPages\Contracts\DataProviderInterface;

/**
 * Provides US states data
 */
class StatesProvider implements DataProviderInterface {
    /**
     * States data cache
     *
     * @var array|null
     */
    private ?array $data = null;

    /**
     * Get all states data
     *
     * @return array
     */
    public function getAll(): array {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return $this->data;
    }

    /**
     * Get data for a specific state
     *
     * @param  string  $key  State name
     *
     * @return mixed|null
     */
    public function get( string $key ): mixed {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return $this->data[ $key ] ?? null;
    }

    /**
     * Check if state exists
     *
     * @param  string  $key  State name
     *
     * @return bool
     */
    public function has( string $key ): bool {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return isset( $this->data[ $key ] );
    }

    /**
     * Get state names
     *
     * @return array
     */
    public function getKeys(): array {
        if ( null === $this->data ) {
            $this->loadData();
        }
        return array_keys( $this->data );
    }

    /**
     * Get states data
     */
    private function loadData(): void {

        $this->data = [
            'Alabama'        => [ 'cities' => [ 'Birmingham', 'Montgomery', 'Mobile', 'Huntsville', 'Tuscaloosa', 'Hoover', 'Auburn', 'Dothan', 'Madison', 'Decatur' ] ],
            'Alaska'         => [ 'cities' => [ 'Anchorage', 'Fairbanks', 'Juneau', 'Sitka', 'Ketchikan', 'Wasilla', 'Kenai', 'Palmer', 'Kodiak', 'Bethel' ] ],
            'Arizona'        => [ 'cities' => [ 'Phoenix', 'Tucson', 'Mesa', 'Chandler', 'Scottsdale', 'Glendale', 'Gilbert', 'Tempe', 'Peoria', 'Surprise' ] ],
            'Arkansas'       => [ 'cities' => [ 'Little Rock', 'Fayetteville', 'Fort Smith', 'Springdale', 'Jonesboro', 'North Little Rock', 'Conway', 'Rogers', 'Bentonville', 'Pine Bluff' ] ],
            'California'     => [ 'cities' => [ 'Los Angeles', 'San Diego', 'San Jose', 'San Francisco', 'Fresno', 'Sacramento', 'Long Beach', 'Oakland', 'Bakersfield', 'Anaheim' ] ],
            'Colorado'       => [ 'cities' => [ 'Denver', 'Colorado Springs', 'Aurora', 'Fort Collins', 'Lakewood', 'Thornton', 'Arvada', 'Westminster', 'Pueblo', 'Greeley' ] ],
            'Connecticut'    => [ 'cities' => [ 'Bridgeport', 'New Haven', 'Hartford', 'Stamford', 'Waterbury', 'Norwalk', 'Danbury', 'New Britain', 'Bristol', 'Meriden' ] ],
            'Delaware'       => [ 'cities' => [ 'Wilmington', 'Dover', 'Newark', 'Middletown', 'Smyrna', 'Milford', 'Seaford', 'Georgetown', 'Elsmere', 'New Castle' ] ],
            'Florida'        => [ 'cities' => [ 'Jacksonville', 'Miami', 'Tampa', 'Orlando', 'St. Petersburg', 'Hialeah', 'Port St. Lucie', 'Cape Coral', 'Tallahassee', 'Fort Lauderdale' ] ],
            'Georgia'        => [ 'cities' => [ 'Atlanta', 'Augusta', 'Columbus', 'Macon', 'Savannah', 'Athens', 'Sandy Springs', 'Roswell', 'Johns Creek', 'Warner Robins' ] ],
            'Hawaii'         => [ 'cities' => [ 'Honolulu', 'East Honolulu', 'Pearl City', 'Hilo', 'Waipahu', 'Kailua', 'Kaneohe', 'Mililani Town', 'Kahului', 'Ewa Gentry' ] ],
            'Idaho'          => [ 'cities' => [ 'Boise', 'Meridian', 'Nampa', 'Idaho Falls', 'Pocatello', 'Caldwell', 'Coeur d\'Alene', 'Twin Falls', 'Post Falls', 'Lewiston' ] ],
            'Illinois'       => [ 'cities' => [ 'Chicago', 'Aurora', 'Peoria', 'Rockford', 'Joliet', 'Naperville', 'Springfield', 'Elgin', 'Waukegan', 'Cicero' ] ],
            'Indiana'        => [ 'cities' => [ 'Indianapolis', 'Fort Wayne', 'Evansville', 'South Bend', 'Carmel', 'Fishers', 'Bloomington', 'Hammond', 'Gary', 'Lafayette' ] ],
            'Iowa'           => [ 'cities' => [ 'Des Moines', 'Cedar Rapids', 'Davenport', 'Sioux City', 'Iowa City', 'Waterloo', 'Ames', 'West Des Moines', 'Council Bluffs', 'Ankeny' ] ],
            'Kansas'         => [ 'cities' => [ 'Wichita', 'Overland Park', 'Kansas City', 'Olathe', 'Topeka', 'Lawrence', 'Shawnee', 'Manhattan', 'Lenexa', 'Salina' ] ],
            'Kentucky'       => [ 'cities' => [ 'Louisville', 'Lexington', 'Bowling Green', 'Owensboro', 'Covington', 'Hopkinsville', 'Richmond', 'Florence', 'Georgetown', 'Nicholasville' ] ],
            'Louisiana'      => [ 'cities' => [ 'New Orleans', 'Baton Rouge', 'Shreveport', 'Lafayette', 'Lake Charles', 'Kenner', 'Bossier City', 'Monroe', 'Alexandria', 'Houma' ] ],
            'Maine'          => [ 'cities' => [ 'Portland', 'Lewiston', 'Bangor', 'South Portland', 'Auburn', 'Biddeford', 'Sanford', 'Brunswick', 'Scarborough', 'Westbrook' ] ],
            'Maryland'       => [ 'cities' => [ 'Baltimore', 'Frederick', 'Rockville', 'Gaithersburg', 'Bowie', 'Hagerstown', 'Annapolis', 'College Park', 'Salisbury', 'Laurel' ] ],
            'Massachusetts'  => [ 'cities' => [ 'Boston', 'Worcester', 'Springfield', 'Cambridge', 'Lowell', 'Brockton', 'New Bedford', 'Quincy', 'Lynn', 'Fall River' ] ],
            'Michigan'       => [ 'cities' => [ 'Detroit', 'Grand Rapids', 'Warren', 'Sterling Heights', 'Lansing', 'Ann Arbor', 'Flint', 'Dearborn', 'Livonia', 'Troy' ] ],
            'Minnesota'      => [ 'cities' => [ 'Minneapolis', 'Saint Paul', 'Rochester', 'Duluth', 'Bloomington', 'Brooklyn Park', 'Plymouth', 'Maple Grove', 'Woodbury', 'St. Cloud' ] ],
            'Mississippi'    => [ 'cities' => [ 'Jackson', 'Gulfport', 'Southaven', 'Hattiesburg', 'Biloxi', 'Meridian', 'Tupelo', 'Olive Branch', 'Greenville', 'Horn Lake' ] ],
            'Missouri'       => [ 'cities' => [ 'Kansas City', 'Saint Louis', 'Springfield', 'Columbia', 'Independence', 'Lee\'s Summit', 'O\'Fallon', 'St. Joseph', 'St. Charles', 'St. Peters' ] ],
            'Montana'        => [ 'cities' => [ 'Billings', 'Missoula', 'Great Falls', 'Bozeman', 'Butte', 'Helena', 'Kalispell', 'Havre', 'Anaconda', 'Miles City' ] ],
            'Nebraska'       => [ 'cities' => [ 'Omaha', 'Lincoln', 'Bellevue', 'Grand Island', 'Kearney', 'Fremont', 'Hastings', 'Norfolk', 'North Platte', 'Papillion' ] ],
            'Nevada'         => [ 'cities' => [ 'Las Vegas', 'Henderson', 'Reno', 'North Las Vegas', 'Sparks', 'Carson City', 'Fernley', 'Elko', 'Mesquite', 'Boulder City' ] ],
            'New Hampshire'  => [ 'cities' => [ 'Manchester', 'Nashua', 'Concord', 'Derry', 'Rochester', 'Salem', 'Dover', 'Merrimack', 'Londonderry', 'Hudson' ] ],
            'New Jersey'     => [ 'cities' => [ 'Newark', 'Jersey City', 'Paterson', 'Elizabeth', 'Edison', 'Woodbridge', 'Lakewood', 'Toms River', 'Hamilton', 'Trenton' ] ],
            'New Mexico'     => [ 'cities' => [ 'Albuquerque', 'Las Cruces', 'Rio Rancho', 'Santa Fe', 'Roswell', 'Farmington', 'Clovis', 'Hobbs', 'Alamogordo', 'Carlsbad' ] ],
            'New York'       => [ 'cities' => [ 'New York City', 'Buffalo', 'Rochester', 'Yonkers', 'Syracuse', 'Albany', 'New Rochelle', 'Mount Vernon', 'Schenectady', 'Utica' ] ],
            'North Carolina' => [ 'cities' => [ 'Charlotte', 'Raleigh', 'Greensboro', 'Durham', 'Winston-Salem', 'Fayetteville', 'Cary', 'Wilmington', 'High Point', 'Concord' ] ],
            'North Dakota'   => [ 'cities' => [ 'Fargo', 'Bismarck', 'Grand Forks', 'Minot', 'West Fargo', 'Dickinson', 'Williston', 'Mandan', 'Jamestown', 'Wahpeton' ] ],
            'Ohio'           => [ 'cities' => [ 'Columbus', 'Cleveland', 'Cincinnati', 'Toledo', 'Akron', 'Dayton', 'Parma', 'Canton', 'Youngstown', 'Lorain' ] ],
            'Oklahoma'       => [ 'cities' => [ 'Oklahoma City', 'Tulsa', 'Norman', 'Broken Arrow', 'Lawton', 'Edmond', 'Moore', 'Midwest City', 'Enid', 'Stillwater' ] ],
            'Oregon'         => [ 'cities' => [ 'Portland', 'Eugene', 'Salem', 'Gresham', 'Hillsboro', 'Beaverton', 'Bend', 'Medford', 'Springfield', 'Corvallis' ] ],
            'Pennsylvania'   => [ 'cities' => [ 'Philadelphia', 'Pittsburgh', 'Allentown', 'Erie', 'Reading', 'Scranton', 'Bethlehem', 'Lancaster', 'Harrisburg', 'Altoona' ] ],
            'Rhode Island'   => [ 'cities' => [ 'Providence', 'Warwick', 'Cranston', 'Pawtucket', 'East Providence', 'Woonsocket', 'Coventry', 'Cumberland', 'North Providence', 'South Kingstown' ] ],
            'South Carolina' => [ 'cities' => [ 'Charleston', 'Columbia', 'North Charleston', 'Mount Pleasant', 'Rock Hill', 'Greenville', 'Summerville', 'Goose Creek', 'Hilton Head Island', 'Florence' ] ],
            'South Dakota'   => [ 'cities' => [ 'Sioux Falls', 'Rapid City', 'Aberdeen', 'Brookings', 'Watertown', 'Mitchell', 'Pierre', 'Spearfish', 'Brandon', 'Box Elder' ] ],
            'Tennessee'      => [ 'cities' => [ 'Memphis', 'Nashville', 'Knoxville', 'Chattanooga', 'Clarksville', 'Murfreesboro', 'Franklin', 'Jackson', 'Johnson City', 'Bartlett' ] ],
            'Texas'          => [ 'cities' => [ 'Houston', 'San Antonio', 'Dallas', 'Austin', 'Fort Worth', 'El Paso', 'Arlington', 'Corpus Christi', 'Plano', 'Laredo' ] ],
            'Utah'           => [ 'cities' => [ 'Salt Lake City', 'West Valley City', 'Provo', 'West Jordan', 'Orem', 'Sandy', 'Ogden', 'St. George', 'Layton', 'South Jordan' ] ],
            'Vermont'        => [ 'cities' => [ 'Burlington', 'Essex', 'South Burlington', 'Colchester', 'Rutland', 'Bennington', 'Brattleboro', 'Milton', 'Hartford', 'Barre' ] ],
            'Virginia'       => [ 'cities' => [ 'Virginia Beach', 'Norfolk', 'Chesapeake', 'Richmond', 'Newport News', 'Alexandria', 'Hampton', 'Roanoke', 'Portsmouth', 'Suffolk' ] ],
            'Washington'     => [ 'cities' => [ 'Seattle', 'Spokane', 'Tacoma', 'Vancouver', 'Bellevue', 'Kent', 'Everett', 'Renton', 'Federal Way', 'Spokane Valley' ] ],
            'West Virginia'  => [ 'cities' => [ 'Charleston', 'Huntington', 'Morgantown', 'Parkersburg', 'Wheeling', 'Weirton', 'Martinsburg', 'Fairmont', 'Beckley', 'Clarksburg' ] ],
            'Wisconsin'      => [ 'cities' => [ 'Milwaukee', 'Madison', 'Green Bay', 'Kenosha', 'Racine', 'Appleton', 'Waukesha', 'Eau Claire', 'Oshkosh', 'Janesville' ] ],
            'Wyoming'        => [ 'cities' => [ 'Cheyenne', 'Casper', 'Laramie', 'Gillette', 'Rock Springs', 'Sheridan', 'Green River', 'Evanston', 'Riverton', 'Cody' ] ],
        ];
    }
}
