#!/usr/bin/env python
import sys
import time
import logging
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver import ActionChains
from selenium.common.exceptions import TimeoutException
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import StaleElementReferenceException
from selenium.common.exceptions import ElementNotVisibleException

CONST_LOGIN_PATH = '/customer/account/login'

logging.basicConfig(filename='selenium.log', filemode='a', format='%(asctime)s,%(msecs)d %(levelname)s %(message)s', datefmt='%H:%M:%S', level=logging.DEBUG)

error = 0;

def writelog( log ):
	if isinstance(log,str):
		logging.info(log)
	else:
		logging.info(str(log))
	print log
	return

def setError( errorstring ):
	writelog( errorstring )
	global error
	error = 1
	raise Exception(errorstring)
	return

def acceptCookie ( driver ):
	try:
		cookiediv = driver.find_element_by_id('CybotCookiebotDialog')
		if cookiediv.is_displayed():
			cookiebutton = driver.find_element_by_id('CybotCookiebotDialogBodyLevelButtonAccept')
			cookiebutton.click()
	except Exception as e:
			return;
	while 1==1:
		try:
			cookiediv = driver.find_element_by_id('CybotCookiebotDialog')
			if cookiediv.is_displayed():
				continue;
			else:
				return;
		except Exception as e:
				return;

def loadlogin ( driver, baseurl ):
	driver.get(baseurl + CONST_LOGIN_PATH);
	return

def fillandsubmitlogin( driver, username, password ):
	user_box = driver.find_element_by_name('login[username]');
	password_box = driver.find_element_by_name('login[password]');
	user_box.send_keys(username); #add username
	password_box.send_keys(password); #add password
	user_box.submit();
	return

def loadproduct( driver, producturl ):
	driver.get(producturl);
	#driver.get('https://istyle.hu/lead-trend-twist-cable-protector.html?color=470');
	return;

def addproductocart( driver ):
	exceptione = None;
	counter = 0;
	addtocart_button = None;
	while (counter < 3 and addtocart_button is None):
		counter = counter + 1;
		try:
			addtocart_button = driver.find_element_by_id('product-addtocart-button');
		except Exception as e:
			exceptione = e;
	if counter == 3:
		raise TimeoutException('No Such Element');
	if addtocart_button is not None:
		driver.execute_script('const button = document.querySelector(\'#product-addtocart-button\');button.click();')
		#ActionChains(driver).move_to_element(addtocart_button).perform();
		#ActionChains(driver).click().perform();
	return;

def checkAddedCartItem( driver ):
	count = 0
	while count < 3:
		cartelements =	getCartElements(driver)
		if cartelements == 0:
			count = count + 1
			time.sleep(1)
		elif len(cartelements) == 1:
			return;
		else:
			count = count + 1
			time.sleep(1)
	raise Exception('Not one item in cart')
	return;

def removeElementsFromCart( driver ):
	elements = 1
	count = None
	iterationcount = 0
	writelog('INIT')
	while iterationcount < 5:
		writelog('ITERATION' + str(iterationcount))
		try:
			elements = getCartElements(driver)
			if elements == 0:
				writelog('NO ELEMENTS IN CART')
				return
			else:
				if count is None:
					count = len(elements)
					writelog('ELEMENTSCOUNT IS FIRST:' + str(count))
					removeItemFromCartOuter(driver,elements)
						
				else:
					if len(elements) < count:
						count = len(elements)
						writelog('ELEMENTSCOUNT IS LESS:' + str(count))
						removeItemFromCartOuter(driver,elements)
					else:
						writelog('ELEMENTSCOUNT WAITING:' + str(count))
						time.sleep(1)
			iterationcount = 0
		except Exception as e:
			count = None
			writelog('EXCEPTION:' + str(e))
			iterationcount = iterationcount + 1
			continue;
	return;

def removeItemFromCartOuter( driver, elements ):
	writelog('START REMOVE ITEM OUT')
	cartlink = driver.find_element(By.XPATH,"//a[contains(@class, 'showcart')]")
	while not cartlink.is_displayed():
		writelog('CARD NOT LOADED YET')
		continue;
	if not elements[0].is_displayed():
		writelog('CARD NOT VISIBLE CLICK ON IT')
		cartlink.click()
		while not elements[0].is_displayed():
			writelog('CARD NOT VISIBLE YET')
			continue;
	removeItemFromCart(driver, elements[0])

def removeItemFromCart( driver, element ):
	writelog('START REMOVE ITEM')
	writelog('FIND DELETE BUTTON')
	deletebutton = element.find_element(By.XPATH,"//a[contains(@class, 'delete')]")
	writelog('BUTTON DELETE CLICK')
	deletebutton.click()
	writelog('WAIT FOR POPUP')
	WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.XPATH, "//aside")))
	try:
		driver.find_element_by_xpath("//aside//button[@class='btn btn-primary']").click()
	except Exception as e:
		raise Exception('OHH')

def getCartElements( driver ):
	minicart = waitAndGetForCart(driver)
	if minicart is not None:
		WebDriverWait(minicart, 5).until(EC.presence_of_element_located((By.XPATH, "//li[@data-role='product-item']")))
		elements = minicart.find_elements_by_xpath("//li[@data-role='product-item']")
		return elements
	else:
		return 0;

def waitAndGetForCart( driver ):
	counter = 0;
	while (counter < 3):
		minicartoutelement = getMinicartOutElement( driver );
		counter = counter + 1
		try:
			minicart = minicartoutelement.find_element_by_id('mini-cart')
			return minicart
		except Exception as e:
			exceptione = e
		try:
			nonminicart = minicartoutelement.find_element(By.XPATH,"//strong[contains(@class, 'empty')]")
			return None
		except Exception as e:
			exceptione = e

def getMinicartOutElement( driver ):
	buttons = driver.find_element_by_xpath("//div[@data-block='minicart']")
	return buttons;

if len(sys.argv) != 5:
	setError('NOT ALL INPUT PARAMETER FILLED');

program_name = sys.argv[0]
arguments = sys.argv[1:]
if len(arguments) != 4:
	setError('NOT ALL INPUT PARAMETER FILLED');

baseurl = arguments[0]
username = arguments[1]
password = arguments[2]
productpath = arguments[3]
chrome_options = webdriver.ChromeOptions()
chrome_options.add_argument('--headless')
chrome_options.add_argument('--no-sandbox')
#driver = webdriver.Chrome('/home/janos/bin/chromedriver', chrome_options=chrome_options)
driver = webdriver.Chrome(executable_path='/usr/bin/chromedriver', chrome_options=chrome_options, service_args=['--log-path=/tmp/chromedrivertestubuntu.log'])
driver.implicitly_wait(5) # Set page load timeout
try:
	writelog('LOADLOGIN')
	if error == 0:
		try:
			loadlogin(driver, baseurl);
		except TimeoutException as e:
			setError('LOAD LOGIN TIMEOUT');
	writelog('COOKIE')
	if error == 0:
		try:
			acceptCookie(driver);
		except TimeoutException as e:
			setError('COOKIE ACCEPT TIMEOUT');
	writelog('LOGIN')
	if error == 0:
		try:
			fillandsubmitlogin(driver, username, password);
		except TimeoutException as e:
			setError('LOGIN TIMEOUT');
	writelog('REMOVE')
	if error == 0:
		try:
			removeElementsFromCart(driver);
		except TimeoutException as e:
			setError('REMOVE ELEMENTS TIMEOUT');
	writelog('LOADPRODUCT')
	if error == 0:
		try:
			loadproduct(driver, baseurl + productpath);
		except TimeoutException as e:
			setError('LOAD PRODUCT TIMEOUT');
	writelog('ADDPRODUCT')
	if error == 0:
		try:
			addproductocart(driver);
		except TimeoutException as e:
			setError('ADD PRODUCT TIMEOUT');
	time.sleep(3)
	writelog('CHECK')
	if error == 0:
		try:
			checkAddedCartItem(driver);
		except Exception as e:
			raise e;#setError('CHECK ERROR');
except Exception as e:
	driver.quit()
	raise e;
driver.quit()