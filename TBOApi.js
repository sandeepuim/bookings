
const axios = require("axios");
const sequelize = require("../../config/database");
//const { logger } = require("../../utils/logger"); // Assuming you have a logger setup
const { getCredentials } = require('../../config/credentials');
const { getUrls } = require('../../config/urls');
const appSettings = require('../../config/config');
const {ItineraryPackageResponse, FlightSearchResponseModel, FlightSearchResponse, ResultsItemItem, Fare, FareBreakdownItem, SegmentsItemItem, FareRulesItem, PenaltyCharges} = require("../../models/Itinerary/ItineraryPackageResponse");
const fs = require('fs');
const path = require('path');
const moment = require('moment');
const { getActivityAndTransferDetailsByCityCode } = require("../qms/ActivityService");
const { log } = require("console");

// Function to get authentication key from TBO API
const getAuthenticationKey = async (ipAddress='103.102.234.36') => {
    let hotelTokenId;

    try {
        const result = await sequelize.query(
            `SELECT * FROM get_hotel_token_generation()`,
            { type: sequelize.QueryTypes.SELECT }
        );

        const existingToken = result && result[0];
        if (existingToken && existingToken.hotel_token_id) {
            hotelTokenId = existingToken.hotel_token_id;
        } else {
            const credentials = getCredentials();
            const authModel = {
                ClientId: credentials.ApiClientId,
                UserName: credentials.ApiUserName,
                Password: credentials.ApiPassword,
                EndUserIp: '103.102.234.36' 
               // EndUserIp: '49.207.205.11'
            };


            // Log the authentication request
            //console.log(`Authentication Request: ${JSON.stringify(authModel)}`);
            const urls = getUrls();
            const authResponse = await axios.post(urls.authenticate, authModel, {
                headers: { "Content-Type": "application/json" }
            });
            //console.log("urls.authenticate",urls.authenticate);
            
            // Log the authentication response
           // console.log(`Authentication Response: ${JSON.stringify(authResponse.data)}`);

            // Parse the authentication response
            const authResponseModel = authResponse.data;

            if (!authResponseModel || !authResponseModel.TokenId) {
                throw new Error("Failed to generate authentication token");
            }

            hotelTokenId = authResponseModel.TokenId;

            await sequelize.query(
                `SELECT add_hotel_token_generation(:hotelTokenId)`,
                {
                    replacements: { hotelTokenId },
                    type: sequelize.QueryTypes.SELECT,
                }
            );

        }

        return hotelTokenId;
    } catch (error) {
        logger.error(`Error in getAuthenticationKey: ${error.message}`, { stack: error.stack });
        throw error;
    }
};

// const getFlightResults = async (itineraryPackage, ipAddress, tokenId, itinerary) => {
//     try {

//         // Construct the searchModel
//         const searchModel = {
//             EndUserIp: appSettings.WebsiteInTestingMode === "N" ? "103.102.234.36" : "103.102.234.36",
//             TokenId: tokenId,
//             AdultCount: itineraryPackage.roomAndTravellers.reduce((sum, room) => sum + room.countAdults, 0),
//             ChildCount: itineraryPackage.roomAndTravellers.reduce((sum, room) => sum + room.countChilds, 0),
//             InfantCount: itineraryPackage.roomAndTravellers.reduce((sum, room) => sum + room.countInfants, 0),
//             DirectFlight: false,
//             OneStopFlight: false,
//             PreferredAirlines: null,
//             Sources: null,
//             Segments: [],
//         };

//         let originCityCode = itineraryPackage.startingCityCode;
//         let lastDestinationDepartureTime = "";
//         let destinationIndex = 0;
//         let destinationCounter = 0;
//         let tempFlightIndexes = [];
//         //console.log("searchModel1",searchModel);
        
//         // Prepare flight segments
//         itineraryPackage.destinations.forEach((destination, index) => {
//             temp = {};
//             if (destination.destinationIsFlightSearchRequired === "true" && originCityCode !== destination.cityCode) {
//                 const departureTime = `${destination.destinationFlightStartDate.split("T")[0]}T00:00:00`;
//                 const arrivalTime = `${destination.destinationFlightStartDate.split("T")[0]}T00:00:00`;
//                 lastDestinationDepartureTime = `${destination.destinationFlightEndDate.split("T")[0]}T00:00:00`;

//                 searchModel.Segments.push({
//                     Origin: originCityCode,
//                     Destination: destination.cityCode,
//                     FlightCabinClass: 1,
//                     PreferredDepartureTime: departureTime,
//                     PreferredArrivalTime: arrivalTime,
//                 });

//                 temp.OriginalDestinationFlightIndex = destinationIndex;
//                 temp.ShowFlight = true;
//                 destinationIndex++;
//             } else {
//                 temp.OriginalDestinationFlightIndex = destinationIndex;
//                 temp.ShowFlight = false;
//             }

//             originCityCode = destination.cityCode;

//             temp.ModifiedDestinationFlightIndex = destinationCounter;
//             destinationCounter++;
//             tempFlightIndexes.push(temp);
//         });

//         // Return to the start city
//         if (searchModel.Segments.length > 0) {
//             searchModel.Segments.push({
//                 Origin: originCityCode,
//                 Destination: itineraryPackage.startingCityCode,
//                 FlightCabinClass: 1, // Hardcoded
//                 PreferredDepartureTime: lastDestinationDepartureTime,
//                 PreferredArrivalTime: lastDestinationDepartureTime,
//             });

//             tempFlightIndexes.push(
//                 {
//                     OriginalDestinationFlightIndex : destinationIndex,
//                     ModifiedDestinationFlightIndex : destinationCounter,
//                     ShowFlight : true
//             });
//         }

//         // Determine Journey Type
//         const segmentsCount = searchModel.Segments.length;
//         console.log("segmentsCounts",segmentsCount);
        
//         if (segmentsCount === 1) {
//             searchModel.JourneyType = 1; // One Way
//         } else if (
//             segmentsCount === 2 &&
//             searchModel.Segments[0].Origin === searchModel.Segments[1].Destination &&
//             searchModel.Segments[0].Destination === searchModel.Segments[1].Origin
//         ) {
//             searchModel.JourneyType = 2; // Round Trip
//         } else {
//             searchModel.JourneyType = 3; // Multi-City
//         }
//         console.log("JourneyTypes",searchModel.JourneyType);

//         // Send the flight search request
//         const urls = getUrls();

//        console.log("Flight body",searchModel);

//         const response = await axios.post(urls.search, searchModel, {
//             headers: { "Content-Type": "application/json" },
//         });

//         // Map the flight search response to itinerary.flightDetails
//         const productSearchResponseModel = response.data;
//         console.log("productSearchResponseModel",productSearchResponseModel);
        
//         itinerary.flightDetails = new FlightSearchResponseModel();
//         itinerary.flightDetails.response = new FlightSearchResponse();
//         itinerary.flightDetails.response.responseStatus = productSearchResponseModel.Response?.ResponseStatus || 0;
//         itinerary.flightDetails.response.error = {
//             errorCode: productSearchResponseModel.Response?.Error?.ErrorCode || 0,
//             errorMessage: productSearchResponseModel.Response?.Error?.ErrorMessage || "",
//         };
//         itinerary.flightDetails.response.traceId = productSearchResponseModel.Response?.TraceId || "";
//         itinerary.flightDetails.response.origin = productSearchResponseModel.Response?.Origin || "";
//         itinerary.flightDetails.response.destination = productSearchResponseModel.Response?.Destination || "";
//         itinerary.flightDetails.response.results = productSearchResponseModel.Response?.Results?.map((group) => {
//             return group.map((item) => convertKeysToCamelCase(item))
//         }) || [];

//         // // Max Flight Results Filtering
//         const maxFlightResults = parseInt(appSettings.FlightResultCount, 10) || 0;
//         if (maxFlightResults > 0 && itinerary.flightDetails.results) {
//             itinerary.flightDetails.results.forEach(resultGroup => {
//                 const currentResults = resultGroup.length;
//                 if (currentResults > maxFlightResults) {
//                     resultGroup.splice(maxFlightResults); // Trim the results
//                 }
//             });
//         }

//         // return itinerary;
//         itinerary.flightIndexes = tempFlightIndexes;

//         // Set the destination OB result index
//         itinerary.destinationOBResultIndex = response.data.Response?.Results?.[0]?.[0]?.ResultIndex ?? "";

//         // For round trip, set the destination IB result index
//         if (searchModel.JourneyType === 2) {
//             itinerary.destinationIBResultIndex = response.data.Response?.Results?.[response.data.Response.Results.length - 1]?.[0]?.ResultIndex ?? "";
//         }

//         // // Check flight result status
//         if (response.data.Response.ResponseStatus !== 1) {
//             itinerary.response.isSuccess = false;
//             itinerary.response.message = response.data.Response.Error?.ErrorMessage || "Unknown error";
//             return itinerary;
//         }

//         // itinerary.IsFlightRemoved = false;
//         return itinerary;
//         // Process the response
//       //  return response.data;
//     } catch (error) {
//         console.error("Error in getFlightResults:", error);
//         throw error;
//     }
// }


const getFlightResults = async (itineraryPackage, ipAddress, tokenId, itinerary) => {
    try {

        // Construct the searchModel
        const searchModel = {
            EndUserIp: appSettings.WebsiteInTestingMode === "N" ? "103.102.234.36" : "103.102.234.36",
            TokenId: tokenId,
            AdultCount: itineraryPackage.roomAndTravellers.reduce((sum, room) => sum + room.countAdults, 0),
            ChildCount: itineraryPackage.roomAndTravellers.reduce((sum, room) => sum + room.countChilds, 0),
            InfantCount: itineraryPackage.roomAndTravellers.reduce((sum, room) => sum + room.countInfants, 0),
            DirectFlight: false,
            OneStopFlight: false,
            PreferredAirlines: null,
            Sources: null,
            Segments: [],
        };

        let originCityCode = itineraryPackage.startingCityCode;
        let lastDestinationDepartureTime = "";
        let destinationIndex = 0;
        let destinationCounter = 0;
        let tempFlightIndexes = [];
        //console.log("searchModel1",searchModel);
        
        // Prepare flight segments
        itineraryPackage.destinations.forEach((destination, index) => {
            temp = {};
            if (destination.destinationIsFlightSearchRequired === "true" && originCityCode !== destination.cityCode) {
                const departureTime = `${destination.destinationFlightStartDate.split("T")[0]}T00:00:00`;
                const arrivalTime = `${destination.destinationFlightStartDate.split("T")[0]}T00:00:00`;
                lastDestinationDepartureTime = `${destination.destinationFlightEndDate.split("T")[0]}T00:00:00`;

                searchModel.Segments.push({
                    Origin: originCityCode,
                    Destination: destination.cityCode,
                    FlightCabinClass: 1,
                    PreferredDepartureTime: departureTime,
                    PreferredArrivalTime: arrivalTime,
                });

                temp.OriginalDestinationFlightIndex = destinationIndex;
                temp.ShowFlight = true;
                destinationIndex++;
            } else {
                temp.OriginalDestinationFlightIndex = destinationIndex;
                temp.ShowFlight = false;
            }

            originCityCode = destination.cityCode;

            temp.ModifiedDestinationFlightIndex = destinationCounter;
            destinationCounter++;
            tempFlightIndexes.push(temp);
        });

        // Return to the start city
        if (searchModel.Segments.length > 0) {
            searchModel.Segments.push({
                Origin: originCityCode,
                Destination: itineraryPackage.startingCityCode,
                FlightCabinClass: 1, // Hardcoded
                PreferredDepartureTime: lastDestinationDepartureTime,
                PreferredArrivalTime: lastDestinationDepartureTime,
            });

            tempFlightIndexes.push(
                {
                    OriginalDestinationFlightIndex : destinationIndex,
                    ModifiedDestinationFlightIndex : destinationCounter,
                    ShowFlight : true
            });
        }

        // Determine Journey Type
        const segmentsCount = searchModel.Segments.length;
        //console.log("segmentsCounts",segmentsCount);
        
        if (segmentsCount === 1) {
            searchModel.JourneyType = 1; // One Way
        } else if (
            segmentsCount === 2 &&
            searchModel.Segments[0].Origin === searchModel.Segments[1].Destination &&
            searchModel.Segments[0].Destination === searchModel.Segments[1].Origin
        ) {
            searchModel.JourneyType = 2; // Round Trip
        } else {
            searchModel.JourneyType = 3; // Multi-City
        }
       // console.log("JourneyTypes",searchModel.JourneyType);

        // Send the flight search request
        const urls = getUrls();

       //console.log("Flight body",searchModel);

        const response = await axios.post(urls.search, searchModel, {
            headers: { "Content-Type": "application/json" },
        });

        // Map the flight search response to itinerary.flightDetails
        const productSearchResponseModel = response.data;
        //console.log("productSearchResponseModel",productSearchResponseModel);
        
        itinerary.flightDetails = new FlightSearchResponseModel();
        itinerary.flightDetails.response = new FlightSearchResponse();
        itinerary.flightDetails.response.responseStatus = productSearchResponseModel.Response?.ResponseStatus || 0;
        itinerary.flightDetails.response.error = {
            errorCode: productSearchResponseModel.Response?.Error?.ErrorCode || 0,
            errorMessage: productSearchResponseModel.Response?.Error?.ErrorMessage || "",
        };
        itinerary.flightDetails.response.traceId = productSearchResponseModel.Response?.TraceId || "";
        itinerary.flightDetails.response.origin = productSearchResponseModel.Response?.Origin || "";
        itinerary.flightDetails.response.destination = productSearchResponseModel.Response?.Destination || "";
        itinerary.flightDetails.response.results = productSearchResponseModel.Response?.Results?.map((group) => {
            return group.map((item) => convertKeysToCamelCase(item))
        }) || [];

        // // Max Flight Results Filtering
        const maxFlightResults = parseInt(appSettings.FlightResultCount, 10) || 0;
        if (maxFlightResults > 0 && itinerary.flightDetails.results) {
            itinerary.flightDetails.results.forEach(resultGroup => {
                const currentResults = resultGroup.length;
                if (currentResults > maxFlightResults) {
                    resultGroup.splice(maxFlightResults); // Trim the results
                }
            });
        }

        // return itinerary;
        itinerary.flightIndexes = tempFlightIndexes;

        // Set the destination OB result index
        itinerary.destinationOBResultIndex = response.data.Response?.Results?.[0]?.[0]?.ResultIndex ?? "";

        // For round trip, set the destination IB result index
        if (searchModel.JourneyType === 2) {
            itinerary.destinationIBResultIndex = response.data.Response?.Results?.[response.data.Response.Results.length - 1]?.[0]?.ResultIndex ?? "";
        }

        // // Check flight result status
        if (response.data.Response.ResponseStatus !== 1) {
            itinerary.response.isSuccess = false;
            itinerary.response.message = response.data.Response.Error?.ErrorMessage || "Unknown error";
            return itinerary;
        }

        // itinerary.IsFlightRemoved = false;
        return itinerary;
        // Process the response
      //  return response.data;
    } catch (error) {
        console.error("Error in getFlightResults:", error);
        throw error;
    }
}
class HotelProcessor {
    constructor(hotelService, logger) {
        this.hotelService = hotelService;
        this.logger = logger || console;
    }

    async processHotels(tboHotelDetails, request, checkInDate, checkOutDate, nationality, batchSize = 100, maxParallelRequests , maxBatchesToProcess) {
        const filteredHotels = tboHotelDetails.filter(hotel => hotel.HotelRating !== null);
        const partitions = this.partitionList(filteredHotels, batchSize);
        const totalBatches = partitions.length;

        //this.logger.info(`Total hotels after filtering: ${filteredHotels.length}`);
        //this.logger.info(`Total partitions created: ${totalBatches}`);
        //this.logger.info(`Processing maximum of ${maxBatchesToProcess} batches in parallel, with each batch having up to ${maxParallelRequests} parallel requests.`); // Updated log

        let allResponses = [];
        const batchPromises = []; // Array to hold promises for each batch

        const batchesToProcess = partitions.slice(0, maxBatchesToProcess); // Select batches to process

        batchesToProcess.forEach((batch, batchIndex) => { // Use forEach to create promises for selected batches
            const batchPromise = async () => { // Create an async function for each batch processing
                const hotelCodes = batch.map(hotel => hotel.HotelCode).join(",");
                const hotelSearchRequest = this.createHotelSearchRequest(request, checkInDate, checkOutDate, hotelCodes, nationality);

                //this.logger.info(`Time: ${Date.now()}, batch: ${batchIndex + 1}`);
                //this.logger.info(hotelSearchRequest);

                try {
                    const response = await this.hotelService.getHotelDetails(hotelSearchRequest);
                    if (response?.HotelResult) {
                        return response.HotelResult; // Return HotelResult from the promise
                    } else {
                        return []; // Return empty array if no HotelResult
                    }
                } catch (error) {
                    this.logger.error(`Error fetching hotel details for batch ${batchIndex + 1}: ${error.message}`);
                    return []; // Return empty array in case of error
                }
            };
            batchPromises.push(batchPromise()); // Add the promise to the array
        });

        const batchResults = await Promise.all(batchPromises); // Execute all batch promises in parallel and wait for all to resolve

        // Flatten the array of arrays of HotelResults into a single array
        for (const results of batchResults) {
            allResponses.push(...results);
        }

        return allResponses;
    }

    partitionList(hotels, batchSize) {
        return hotels.reduce((acc, hotel, index) => {
            const batchIndex = Math.floor(index / batchSize);
            acc[batchIndex] = acc[batchIndex] || [];
            acc[batchIndex].push(hotel);
            return acc;
        }, []);
    }

    createHotelSearchRequest(baseRequest, checkIn, checkOut, hotelCodes, nationality) {
        return {
            ...baseRequest,
            CheckIn: checkIn,
            CheckOut: checkOut,
            HotelCodes: hotelCodes,
            GuestNationality: nationality
        };
    }
}


async function getHotelSearchResults(itineraryPackage, ipAddress, tokenId, itinerary) {
    let itineraryDestinations = [];
    const urls = getUrls();
    const credentials = getCredentials();
    const auth = Buffer.from(`${credentials.ApiUserName}:${credentials.ApiPassword}`).toString('base64');
    const authTest = Buffer.from(`travelcategory:Tra@59334536`).toString('base64');
//const authTest = Buffer.from(`TBOStaticAPITest:Tbo@11530818`).toString('base64');

    const hotelService = { // Define hotelService here
        getHotelDetails: async (hotelSearchRequest) => {
            console.log(`Time: ${Date.now()}, API Request:`, JSON.stringify(hotelSearchRequest, null, 2));

            try {
                const hotelSearchResponse = await axios.post(urls.hotelSearchUrl, JSON.stringify(hotelSearchRequest), {
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": `Basic ${auth}`
                    },
                });
               // console.log("hotelSearchResponse1:",hotelSearchResponse);
                return hotelSearchResponse.data;
            } catch (error) {
                console.error("Error in hotelService.getHotelDetails:", error);
                throw error;
            }
        }
    };

    // Initialize HotelProcessor
    const hotelProcessor = new HotelProcessor(hotelService, console); // Using console as logger for now

    for (let destination of itineraryPackage.destinations) {
        let maxHotelResults = 0;
        maxHotelResults = parseInt(appSettings.HotelResultCount, 10) || 0;
        
        let TBOhotelDetailResponse = await axios.post(urls.TBOHotelCodeList, {
            "CityCode": destination.cityId,
            "IsDetailedResponse": true
        }, {
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Basic ${authTest}`
            },
        });
        //console.log("TBOhotelDetailResponse1",TBOhotelDetailResponse);    
        if( TBOhotelDetailResponse?.data?.Status?.Code !== 200){
            console.log("Unable to fetch TBOhotelDetails");
            if (!itinerary.Response) {
                itinerary.Response = {};
            }

            itinerary.Response.IsSuccess = false;
            itinerary.Response.Message = TBOhotelDetailResponse?.data?.Status?.Description || "Unknown error occurred";
            continue; // Skip to the next destination if hotel details fetch fails
        }
        
        let TBOhotelDetails = TBOhotelDetailResponse?.data?.Hotels;
       
        const filteredHotels = TBOhotelDetails?.filter(hotel =>
            ['ThreeStar', 'FourStar', 'FiveStar'].includes(hotel.HotelRating)
          );
         console.log("filteredHotels2",filteredHotels);  
        let hotelCodes = filteredHotels?.map(hotel => hotel.HotelCode) || [];

        // No need to limit hotelCodes to 300 here as HotelProcessor will only process max 3 batches

        let aggregatedHotelSearchResults = [];

        // Hotel search parameters - Base request parameters
        const checkInDate = moment(destination.destinationStartDate).format("YYYY-MM-DD");
        const checkOutDate = moment(destination.destinationEndDate).format("YYYY-MM-DD");
        const paxRooms = itineraryPackage.roomAndTravellers.map(room => ({
            Adults: room.countAdults || 0,
            Children: room.countChilds || 0,
            ChildrenAges: room.countChilds ? Array.from({ length: room.countChilds }, (_, i) => 2 + i * 3).map(age => (age > 11 ? 11 : age)) : null
        }));
        const noOfRooms = itineraryPackage.roomAndTravellers.length;

        const baseHotelSearchRequest = { // Base request object
            GuestNationality: "IN", //destination.countryCode.trim(),
            PaxRooms: paxRooms,
            ResponseTime: 15.0,
            IsDetailedResponse: true,
            Filters: {
                Refundable: true,
                NoOfRooms: noOfRooms,
                MealType: 0,
                OrderBy: 0,
                StarRating: 28,
                HotelName: null
            },
            EndUserIp: appSettings.WebsiteInTestingMode === "Y" ? "192.168.10.10" : ipAddress,
            TokenId: tokenId,
        };


        // Process hotels using HotelProcessor, limit to 3 batches
        const batchSearchResults = await hotelProcessor.processHotels(
            TBOhotelDetails, // Pass full TBOhotelDetails for static info lookup later
            baseHotelSearchRequest,
            checkInDate,
            checkOutDate,
            "IN", //destination.countryCode.trim()
            50, // batch size
            10,  // max parallel requests
            10    // max batches to process - added limit here
        );

        aggregatedHotelSearchResults = batchSearchResults;


        if (aggregatedHotelSearchResults.length > 0) {
            let processedHotelResults = [];
            let i = 0;
            for (let result of aggregatedHotelSearchResults) {
                let staticHotelInfo = TBOhotelDetails.find(x => x.HotelCode === result.HotelCode);

                if (staticHotelInfo) {
                    result.resultIndex  = i;
                    result.hotelName = staticHotelInfo.HotelName || result.HotelName;
                    result.hotelCode = staticHotelInfo.HotelCode || result.HotelCode;
                    result.hotelAddress = staticHotelInfo.Address || result.HotelAddress;
                    result.starRating = parseInt(staticHotelInfo.TripAdvisorRating) || 1;
                    result.hotelRating = staticHotelInfo.HotelRating || 'NA';
                    result.hotelDescription = staticHotelInfo.Description || result.HotelAddress;

                    let hotelStaticDataImages = staticHotelInfo?.images || [];
                    if (hotelStaticDataImages.length > 0) {
                        result.hotelPicture = hotelStaticDataImages[0]?.Paragraph?.find(z => z.Type === "Thumbnail")?.URL || result.HotelPicture;
                    }
                }
                    //console.log("hResult:",result?.Rooms);
                result.roomDetails = result?.Rooms?.map((room, index) => ({
                    bookingCode : room.BookingCode,
                    inclusion : room.Inclusion,
                    dayRates : room.DayRates,
                    availabilityType: "Confirm",
                    categoryId: room.BookingCode.split("!")[0],
                    childCount: itineraryPackage.roomAndTravellers[index]?.Children || 0,
                    requireAllPaxDetails: true,
                    roomId: 0,
                    roomStatus: 0,
                    roomIndex: index + 1,
                    roomTypeCode: room.BookingCode,
                    roomDescription: room.Name[0] || "Standard Room",
                    roomTypeName: room.Name[0] || "Standard Room",
                    ratePlanCode: room.BookingCode,
                    ratePlan: 0,
                    ratePlanName: room.MealType || "Room Only",
                    infoSource: "FixedCombination",
                    sequenceNo: "AG~~" + room.BookingCode.split("!")[0] + "~0",
                    dayRates: room.DayRates[0] || null,
                    isPerStay: true,
                    supplierPrice: null,
                    price: {
                        currencyCode: "INR",
                        roomPrice: room.DayRates.flat().reduce((acc, curr) => acc + curr.BasePrice, 0), // Summing base prices
                        tax: room.TotalTax || 0,
                        extraGuestCharge: 0.0,
                        childCharge: 0.0,
                        otherCharges: 0.0,
                        discount: 0.0,
                        publishedPrice: room.TotalFare,
                        publishedPriceRoundedOff: Math.round(room.TotalFare),
                        offeredPrice: room.TotalFare,
                        offeredPriceRoundedOff: Math.round(room.TotalFare),
                        agentCommission: 0.0,
                        agentMarkUp: 0.0,
                        serviceTax: room.TotalTax * 0.12, // Approx 12% service tax assumption
                        tcs: 0.0,
                        tds: 0.0,
                        serviceCharge: 0.0,
                        totalGSTAmount: room.TotalTax,
                        gst: {
                            cgstAmount: 0.0,
                            cgstRate: 0.0,
                            cessAmount: 0.0,
                            cessRate: 0.0,
                            igstAmount: room.TotalTax,
                            igstRate: 18.0,
                            sgstAmount: 0.0,
                            sgstRate: 0.0,
                            taxableAmount: room.DayRates[0][0].BasePrice * 0.18 // Approx 18% IGST assumption
                        }
                    },
                    cancellationPolicy: room.CancelPolicies.map(policy => ({
                        fromDate: policy.FromDate,
                        chargeType: policy.ChargeType,
                        cancellationCharge: policy.CancellationCharge
                    }))
                })) || [];
                processedHotelResults.push(result);
                i++;
            }


            // Get Room Details for selected hotel - Assuming you still want to do this for the first hotel result
            let roomRequestmodel = {};
            if (processedHotelResults.length > 0) {
                 roomRequestmodel = {
                    ResultIndex: processedHotelResults[0]?.ResultIndex,
                    HotelCode: processedHotelResults[0]?.HotelCode,
                    EndUserIp: appSettings.WebsiteInTestingMode === "Y" ? "192.168.10.10" : ipAddress, // Use ipAddress here
                    TokenId: tokenId,
                    TraceId: aggregatedHotelSearchResults[0]?.TraceId, // Or use from first batch response if available and relevant
                };

                let roomsSearchResponse = {}; // This should contain the actual response
                // let roomsSearchResponseModel = roomsSearchResponse?.data; // You need to actually call the API to get room details if needed

                // processedHotelResults[0].roomCombinations = roomsSearchResponseModel?.GetHotelRoomResult?.RoomCombinations || '';
                // processedHotelResults[0].roomCombinationsArray = roomsSearchResponseModel?.GetHotelRoomResult?.RoomCombinationsArray || '';
            }


            let itineraryDestination = {
                destinationIndex: destination.destinationIndex,
                destinationStartDate: destination.destinationStartDate,
                destinationEndDate: destination.destinationEndDate,
                destinationNights: destination.destinationNights,
                destinationDayTitle: "DestinationDayTitle",
                destinationCity: {
                    cityId: destination.cityId,
                    cityName: destination.cityName,
                    cityCode: destination.cityCode,
                    countryName: destination.countryName,
                    countryCode: destination.countryCode,
                },

                hotel: {
                    hotelSearchResult: {
                        responseStatus: 1,
                        error: {
                            errorCode: 0,
                            errorMessage: "",
                        },
                        traceId: aggregatedHotelSearchResults[0]?.TraceId || "", // Or from first batch response
                        cityId: destination.cityId,
                        remarks: "",
                        checkInDate: checkInDate, // Use pre-calculated value here
                        checkOutDate: checkOutDate, // Use pre-calculated value here
                        preferredCurrency: "INR",
                        noOfRooms: noOfRooms, // Use pre-calculated value here
                        hotelResults: processedHotelResults, // Use processedHotelResults here
                    },
                },
                destinationHotel: {
                    hotelResultIndex: processedHotelResults[0]?.ResultIndex || 0,
                    hotelCode: processedHotelResults[0]?.HotelCode,
                    hotelRoomIndex: processedHotelResults[0]?.Rooms[0]?.RoomIndex || 0,
                    hotelRoomTypeCode: processedHotelResults[0]?.Rooms[0]?.RoomTypeCode || "",
                    selectedHotelRoomIndex: getHotelRoomIndex(processedHotelResults), // Pass processedHotelResults
                    selectedHotelRoomTypeCode: getHotelRoomTypeCode(processedHotelResults[0]?.Rooms), // Pass Rooms from processed results
                },
                activities: await getActivities(destination),
            };

            itineraryDestinations.push(itineraryDestination);
        } else {
            if (!itinerary.Response) {
                itinerary.Response = {};
            }

            itinerary.Response.IsSuccess = false;
            itinerary.Response.Message = "No hotel results found after processing batches within the limit."; // Indicate no results after batch processing
        }
    }

    itinerary.destinations = itineraryDestinations;

    return itinerary;
}
function transformNewApiResponse(newApiResponse) {
    return newApiResponse.map(room => ({
        availabilityType: "Confirm",
        categoryId: room.BookingCode.split("!")[0], // Extracting ID
        childCount: 0,
        requireAllPaxDetails: true,
        roomId: 0,
        roomStatus: 0,
        roomIndex: 1,
        roomTypeCode: room.BookingCode,
        roomDescription: room.Name[0] || "Standard Room",
        roomTypeName: room.Name[0] || "Standard Room",
        ratePlanCode: room.BookingCode,
        ratePlan: 0,
        ratePlanName: room.MealType || "Room Only",
        infoSource: "FixedCombination",
        sequenceNo: "AG~~" + room.BookingCode.split("!")[0] + "~0",
        dayRates: room.DayRates[0] || null,
        isPerStay: true,
        supplierPrice: null,
        totalFare: room.TotalFare,
        totalTax: room.TotalTax,
        mealType: room.MealType,
        isRefundable: room.IsRefundable,
        withTransfers: room.WithTransfers,
        price: {
            currencyCode: "INR",
            roomPrice: room.DayRates.flat().reduce((acc, curr) => acc + curr.BasePrice, 0),
            tax: room.TotalTax || 0,
            extraGuestCharge: 0.0,
            childCharge: 0.0,
            otherCharges: 0.0,
            discount: 0.0,
            publishedPrice: room.TotalFare,
            publishedPriceRoundedOff: Math.round(room.TotalFare),
            offeredPrice: room.TotalFare,
            offeredPriceRoundedOff: Math.round(room.TotalFare),
            agentCommission: 0.0,
            agentMarkUp: 0.0,
            serviceTax: room.TotalTax * 0.12, // Approx 12% service tax assumption
            tcs: 0.0,
            tds: 0.0,
            serviceCharge: 0.0,
            totalGSTAmount: room.TotalTax,
            gst: {
                cgstAmount: 0.0,
                cgstRate: 0.0,
                cessAmount: 0.0,
                cessRate: 0.0,
                igstAmount: room.TotalTax,
                igstRate: 18.0,
                sgstAmount: 0.0,
                sgstRate: 0.0,
                taxableAmount: room.DayRates[0][0].BasePrice * 0.18 // Approx 18% IGST assumption
            }
        },
        cancellationPolicy: room.CancelPolicies.map(policy => ({
            fromDate: policy.FromDate,
            chargeType: policy.ChargeType,
            cancellationCharge: policy.CancellationCharge
        }))
    }));
}
function getHotelRoomIndex(hotelSearchResponseModel) {
    // Implement logic to return hotel room index
    return 0; // Placeholder
}

function getHotelRoomTypeCode(roomDetails) {
    // Implement logic to return room type code
    return roomDetails && roomDetails.length > 0 ? roomDetails[0].RoomTypeCode : ""; // Placeholder
}

async function getActivities(destination) {
    // Implement logic to fetch activities from your DB or API
    const activities = await getActivityAndTransferDetailsByCityCode(destination.cityCode);

    return activities['data'];
}

async function readXmlFile(filePath) {
    // Implement logic to read XML file
    return []; // Placeholder
}

const toCamelCase = (str) => {
    // Check if the first two characters are uppercase, return as is if true
    if (str.length > 1 && str[0] === str[0].toUpperCase() && str[1] === str[1].toUpperCase()) {
        return str;
    }
    return str.charAt(0).toLowerCase() + str.slice(1);
};

const convertKeysToCamelCase = (obj) => {
    if (Array.isArray(obj)) {
        return obj.map((item) => convertKeysToCamelCase(item));
    } else if (obj !== null && typeof obj === "object") {
        return Object.keys(obj).reduce((acc, key) => {
            const camelKey = toCamelCase(key);
            acc[camelKey] = convertKeysToCamelCase(obj[key]);
            return acc;
        }, {});
    }
    return obj;
};

module.exports = {
    getAuthenticationKey,
    getFlightResults,
    getHotelSearchResults
};
